# ROADMAP — Réécriture complète MesPronos → Drupal 11 / PHP 8.4

> Document de référence de la réécriture (branche cible **`3.0.x`**).
> Épic de suivi : **[#120](https://github.com/mespronos/mespronos/issues/120)** — tickets détaillés : **#121 → #132**.
>
> Ce fichier est la **source de vérité** du plan. Toute évolution validée en review de cette PR est répercutée **ici** *et* dans les tickets GitHub correspondants.

---

## 1. Contexte

`mespronos` est un module Drupal **8** (annoncé `core: 8.x`, sans `core_version_requirement`) de pronostics sportifs, en production sur mespronos.net avec des données réelles. Il date de 2016, n'a jamais dépassé l'alpha (`8.x-2.0-dev`), et Drupal 8 est en fin de vie depuis 2021 : le module n'est pas installable sur D9+.

L'analyse (123 fichiers PHP, ~11 700 lignes) révèle une dette technique structurelle qui justifie une **réécriture from-scratch** plutôt qu'un rafistolage :

- **Pattern « entité-as-repository »** : ~56 méthodes statiques sur les entités font des requêtes + effets de bord (`RankingDay::createRanking()`, `Bet::getUserBetsForGames()`, `League::close()`…).
- **Service-locator partout** : 50+ appels statiques `\Drupal::` au lieu d'injection de dépendances ; `LeagueManager` charge le mauvais storage.
- **SQL brut** avec noms de tables en dur + `db_select()`/`db_query()` dépréciés.
- **Controllers obèses**, 34 méthodes statiques, HTML en dur, logique métier dans les formulaires, blocs qui instancient des controllers.
- **APIs dépréciées** : `drupal_set_message()`, `\Drupal::l()`, `render()`, `t()` dans les services, `getInfo()`.
- **Tests** : 15 tests SimpleTest/`WebTestBase` cassés sur D9+ (~3000 l.), 3 seuls tests PHPUnit modernes. CI en QA-only sur image PHP 7.4, sans exécution de tests.

**Décisions cadrées :** réécriture **from-scratch**, cible **Drupal 11 / PHP 8.4+**, modèle de données révisable, refonte du front (SDC), tests modernisés, groupes via **`drupal/group`** (plutôt que le sous-module custom), données de prod **migrées** via l'API Migrate.

**Résultat visé :** une base de code propre, en couches strictes, installable sur D11, testée et en CI, fonctionnellement à parité, avec une migration de données vérifiable.

---

## 2. Architecture cible

Direction de dépendance stricte : **Présentation → Service (domaine) → Repository → Entité**. Les entités ne contiennent que des données. Les services n'émettent jamais de HTML. Les repositories possèdent toutes les requêtes.

### 2.1 Découpage en modules (un seul package Composer `drupal/mespronos`)

```
mespronos/                 base : entités, repositories, services domaine, présentation cœur, classements
  modules/
    mespronos_notification/  Reminder + planification/destinataires/mailer + cron + hook_mail
    mespronos_import/        FormImport + ImporterController → service d'import
    mespronos_group/         adaptateur fin au-dessus de drupal/group
    mespronos_migrate/       plugins migrate (source/process/destination) + configs de migration
```

Les classements restent dans **base** (cœur du domaine) mais isolés dans `src/Domain/Ranking/` + `src/Repository/`. **Ils ne sont plus des entités** (cf. §2.4). Notifications et import sont réellement optionnels → sous-modules.

### 2.2 Couche entités (D11)

- Les **7 entités de contenu** (Sport, Team, League, Day, Game, Bet, Reminder) définies avec **attributs PHP** `#[ContentEntityType(...)]` (remplace les annotations `@ContentEntityType`), **data-only** : `baseFieldDefinitions()`, getters/setters typés, `preCreate` pour l'ownership. Aucune requête, aucun `\Drupal::`, aucun markup.
- Handlers (`access`, `list_builder`, `views_data`, `form`, `view_builder`, `route_provider`) via la map `handlers` de l'attribut. `MPNContentEntityBase` reste une base fine.
- **Améliorations de modèle (mappables 1:1 en migration, wrappers sur les mêmes colonnes)** :
  - `enum BettingType: string { Score, Winner }`, `enum LeagueStatus: string { Future, Active, Over, Archived }`, `enum MatchOutcome { Team1Win, Team2Win, Draw }` (remplacent les tableaux statiques `League::$betting_types`/`$status_allowed_value`).
  - Value objects `readonly` : `Domain\Score(team1, team2)` avec `winner(): MatchOutcome`, `Domain\PointRules(scoreFound, winnerFound, participation)`.
  - Renommer `isScoreSetted()` → `isScoreSet()`.
- **Tables** : renommer en `mespronos_<entity>` (simple underscore, idiomatique D11) — les données arrivent par Migrate de toute façon.
- Les 3 ex-entités `RankingDay/League/General` sont **supprimées** au profit d'une table d'agrégats (§2.4).

### 2.3 Repositories (remplacent les méthodes statiques d'entités)

| Service id | Classe | Remplace |
|---|---|---|
| `mespronos.repository.bet` | `Repository\BetRepository` | `Bet::getUserBetsForGames()`, `BetManager` (SQL brut) |
| `mespronos.repository.game` | `Repository\GameRepository` | `Game::getGamesForDay()`, requête « upcoming » de NotificationManager |
| `mespronos.repository.day` | `Repository\DayRepository` | statiques `DayController::getNextDaysToBet()/getLastDays()` |
| `mespronos.repository.league` | `Repository\LeagueRepository` | `LeagueManager` (corrige le bug de storage), SQL de `LeagueGettersTrait` |
| `mespronos.repository.ranking` | `Repository\RankingRepository` | **toutes** les statiques de `RankingBase`/`RankingDay`/`RankingGeneral` ; lit/écrit la table d'agrégats `mespronos_ranking_points` ; filtrage groupe/`bet_private` centralisé **une seule fois** (`applyGroupScope()`) ; **position calculée en lecture** par fonction fenêtre SQL ; écritures **incrémentales par delta** |

Les repositories renvoient des entités ou des DTO typés, jamais des render arrays.

### 2.4 Classements — table d'agrégats (et non entités)

> **Source de vérité unique = les `points` portés par chaque `Bet`.** Tout classement s'en dérive. On **supprime** les 3 entités de contenu `RankingDay/League/General` (révisionnables, lourdes, réécrites pour tout le monde à chaque score, données dérivées à migrer ; position calculée en PHP en chargeant tout le classement en mémoire — cf. `src/Entity/Base/RankingBase.php` `determinePosition()` l.29 et `getRanking()` l.63) au profit d'**une table dénormalisée** :
>
> ```
> mespronos_ranking_points(scope_type, scope_id, uid, points, games_betted)
>    PRIMARY KEY (scope_type, scope_id, uid)     scope_type ∈ {day, league, general}
> ```
>
> - **Une table pour les 3 portées** (general = `scope_id 0`), schéma simple (pas l'API Entity → pas de révisions/Field API par ligne).
> - **Écritures incrémentales et bornées** : à la saisie d'un score, on ne touche **que les users ayant parié sur ce match** ; pour chaque pari on applique le **delta** sur ses 3 lignes (`UPDATE … points = points + :delta`). Plus de « recalculer tout le monde ».
> - **Position JAMAIS stockée** (c'est elle qui change pour tous quand un seul user bouge) : calculée à la lecture via `RANK() OVER (ORDER BY points DESC)` (MariaDB 10.2+/MySQL 8, OK D11), ex æquo natifs, pagination directe ; page **cachée** par tags invalidés à la saisie de score.
> - **Tradeoff assumé** : perte de l'intégration Views/Field UI gratuite — sans intérêt pour une donnée calculée en lecture seule affichée via controller + SDC.
> - *Variante plus simple si la lecture reste bon marché* : pas de table d'agrégats, classements en pur `SUM(points) GROUP BY uid` + fonction fenêtre, cachés (zéro coût d'écriture). La table d'agrégats n'est alors qu'une optimisation de lecture.

### 2.5 Services domaine

| Service id | Classe | Responsabilité |
|---|---|---|
| `mespronos.scoring` | `Service\ScoringService` | **L'algorithme de points** extrait tel quel de `BetController::updateBetFromGame()` (`src/Controller/BetController.php:41-61`) : score exact → `points_score_found`, bon vainqueur/nul → `points_winner_found`, sinon → `points_participation`. Pur, unit-testable. |
| `mespronos.bet_placement` | `Service\BetPlacementService` | Pose/validation des paris (logique de `GamesBetting::submitForm`), contrôle des deadlines. |
| `mespronos.ranking` | `Service\RankingService` | applique les **deltas incrémentaux** sur `mespronos_ranking_points` (remplace les 3 `createRanking()` statiques qui réécrivaient tout) ; `rebuild()` complet pour réimport/réparation ; dispatch `RankingsRecomputedEvent`. **Aucune position stockée** (calcul en lecture). |
| `mespronos.results` | `Service\ResultService` | Applique le score d'un match → `ScoringService` sur les paris concernés → mise à jour des agrégats. C'est l'orchestration aujourd'hui inline dans `GamesMarks::submit`. **Doit tenir la charge** (cf. §2.6). |
| `mespronos.league_lifecycle` | `Service\LeagueLifecycleService` | `close()`/archive sorti de l'entité `League`. |
| `mespronos.statistics` | `Service\StatisticsService` | depuis `StatisticsManager`, en full DI. |
| `mespronos.domain_resolver` | `Service\DomainResolver` | fusionne la résolution user/domaine dupliquée entre `UserManager` et `MespronosDomainManager`. |

**`NotificationManager` (god class 291 l.) décomposé** dans `mespronos_notification` : `ReminderScheduler` (cron, config, garde « déjà envoyé », state), `ReminderRecipientResolver` (qui notifier), `ReminderMailer` (rendu + envoi, **timezone via config** et non `Europe/Paris` en dur). Toutes les deps injectées par promotion de constructeur.

### 2.6 Montée en charge — saisie de score (risque principal)

À la saisie d'un score, `ResultService` re-score les paris du match puis met à jour les classements. Sur une ligue très suivie le traitement synchrone inline actuel (`GamesMarks::submit`) peut timeout / saturer la mémoire. Conception cible :

- **Périmètre borné** : on ne touche **que les paris du match** et, pour leurs auteurs, les **3 lignes d'agrégat** (day/league/general) via **delta** — pas de réécriture de tout le classement. La **position n'est pas recalculée** : elle est dérivée à la lecture.
- **Aucun chargement intégral en mémoire** : itérer les paris par **chunks** (IDs via repository, lots de N, `resetCache` entre lots) ; mises à jour SQL groupées.
- **`ScoringService` reste pur** (re-score un pari isolément) ; l'**orchestration de masse** est portée par `ResultService` et exécutée :
  - en **Batch API** quand déclenchée depuis un formulaire admin (saisie de scores, recompute d'une ligue) → barre de progression, pas de timeout ;
  - en **Queue API** (worker sur cron) pour le traitement asynchrone hors interface ;
  - en **Drush batché** pour `mespronos:recompute-rankings` (rebuild complet).
- Test de charge dédié (cf. §3).

### 2.7 Présentation + front

- **Controllers** fins : injectent services/repositories via `create()`, renvoient uniquement des render arrays.
- **Blocs** : interdiction d'instancier des controllers ; `LastBets`/`NextBets` injectent les repositories. Supprimer le `new LastBetsController()` de `mespronos.module:334`.
- **Formulaires** : `GamesBetting`/`GamesMarks` délèguent à `BetPlacementService`/`ResultService` ; plus de `createRanking()` ni `Cache::invalidateTags()` manuel → déplacés dans un **event subscriber** réagissant à `RankingsRecomputedEvent`/`GameScoreDefinedEvent`.
- **Front en Single Directory Components (SDC, natif D11)** sous `components/` : `mespronos:game`, `game-flag`, `ranking-table`, `ranking-row`, `podium`, `day-card`, `user-palmares`, `bet-edit-link`. Chaque `'#markup' => '<a class="picto">…'` et `t('<span…>')` migre vers un SDC. Les view builders produisent des render arrays embarquant les SDC via `#type: component`. Remplacer `render()`/`\Drupal::l()` par `#type => 'link'` / `Url` / `{{ link() }}`.

### 2.8 Groupes — `drupal/group` 3.x

**Adopter `drupal/group` (Group 3.x, compatible D11) avec un adaptateur fin `mespronos_group`**, plutôt que porter l'entité Group custom (~700 l. de code supprimées : membership, join/leave, accès, listes sont fournis par contrib).

L'adaptateur conserve uniquement le spécifique MesPronos :
- un `GroupType` « mespronos_group » + plugin de relation pour l'adhésion (remplace `field_group` sur l'utilisateur) ;
- les **classements par groupe** via l'unique `RankingRepository::applyGroupScope(GroupInterface $group)` (filtre sur les `uid` membres, requêtés via le storage de relations de Group) ;
- code d'accès à l'inscription + mail « nouveau groupe » (event subscriber sur création de groupe + alter du formulaire d'inscription).

*Repli si l'adoption de contrib Group s'avère trop lourde au cutover : entité Group custom minimale (id, label, access_code, owner) + champ membership. Recommandation = contrib Group.*

### 2.9 Migration de données (API Migrate) — `mespronos_migrate`

- **Source** : les anciennes tables `mespronos__*` lues via une connexion DB secondaire (clé `migrate` dans `settings.php`). Un plugin source `SqlBase` par table (`src/Plugin/migrate/source/`).
- **Destination** : plugins standards `entity:<id>`. Remappage des FK via `migration_lookup` ; normalisation des enums via un petit process plugin custom.
- **Configs** `migrate_plus.migration.mespronos_*.yml` avec `migrate_plus` + `migrate_tools`. **Ordre de dépendances** : `sport → team → league → day → game → bet` ; `reminder` après `day` ; users supposés présents (sinon migration `users` d'abord + `migration_lookup`).
- **Classements : RECALCULÉS, pas migrés** (données dérivées) ; aucune ex-table `ranking_*` à migrer. Post-import : commande Drush `mespronos:recompute-rankings --all` qui **reconstruit la table d'agrégats** depuis les `points` des paris (`RankingService::rebuild()`, batché). Commande `mespronos:verify-points` qui **diffe les points recalculés vs la colonne `points` héritée des paris** — gate de validation prouvant que `ScoringService` reproduit la prod.

---

## 3. Tests

**Objectif : maximiser la couverture par des tests unitaires.** La séparation en couches est pensée pour ça : un maximum de logique métier doit vivre dans des classes **pures et testables sans Drupal** (services domaine, value objects, enums, résolution de destinataires, calcul de delta de classement, application des règles de points). Les repositories restent fins (requêtes uniquement) ; toute la logique décisionnelle remonte dans des services injectables et mockables. Cible de **couverture ≥ 85 %** sur `src/Domain/` et `src/Service/`, mesurée en CI et bloquante.

- **Unit** (`tests/src/Unit/`) — priorité absolue :
  - `ScoringServiceTest` (tous les paliers et cas limites du legacy `MespronosBetPointsTest`) — **le test le plus important**, il fige la règle de prod.
  - Value objects / enums : `Score` (`winner()`, égalité), `PointRules`, `BettingType`, `LeagueStatus`, `MatchOutcome`.
  - `RankingService` : calcul du **delta incrémental** (points/games_betted pour un pari re-scoré) extrait en méthode pure. (La **position** est gérée en SQL par fonction fenêtre → couverte en Kernel, pas en Unit.)
  - `ReminderRecipientResolver` : filtrage « qui notifier » (paris manquants, garde « déjà envoyé », fenêtre horaire) testé en pur avec repositories mockés.
  - `ResultService` : logique d'**orchestration par lots** (découpage en chunks, sélection des lignes d'agrégat impactées) testée avec collaborateurs mockés.
- **Kernel** (`tests/src/Kernel/`) : `RankingServiceKernelTest` (paris → scores → agrégats + position lue), `BetPlacementServiceKernelTest` (deadlines, bulk), `BetRepository`/`RankingRepositoryKernelTest` (position via fonction fenêtre dont ex æquo, `applyDelta`, `rebuild`, scope groupe), `LeagueLifecycleKernelTest`, `ReminderSchedulerKernelTest`. Nouveau `MespronosKernelTestBase` avec fabrique de fixtures Sport→…→Bet.
- **Test de charge** (`ScoringScaleKernelTest`) : match avec un **grand nombre de paris** (5 000+), déclencher `ResultService` et vérifier que le re-scoring + deltas s'exécutent par lots sans charger tout en mémoire et dans une enveloppe temps/mémoire raisonnable.
- **Functional** (`tests/src/Functional/`) : `GamesBettingFormTest`, `GamesMarksFormTest` (submit → points + classements + cache tags), `RankingPageTest`, `DayPageTest`, `GroupRankingTest`.
- Supprimer les 15 fichiers `src/Tests/*`.

---

## 4. Qualité & CI — deux pipelines

Tests **et** qualité de code doivent tourner sur les **deux** plateformes, à partir des mêmes configs racine partagées (`phpstan.neon`, `phpcs.xml.dist`, `phpunit.xml.dist`) — pas de divergence de règles.

1. **CI Drupal.org (GitLab)** — templates GitLab CI Drupal (`drupal/gitlab-templates`). Jobs : `phpcs`, `phpstan`, `phpunit` (Unit + Kernel + Functional), `composer-lint`.
2. **GitHub Actions** (`.github/workflows/ci.yml`) — pipeline équivalente : install Composer puis **phpcs + phpstan + phpunit** sur PHP 8.3 + 8.4 / Drupal 11.x (service MariaDB).

**QA (identique sur les deux) :**
- **phpcs** aux normes **`Drupal` + `DrupalPractice`** (paquet `drupal/coder`).
- **phpstan niveau 5** avec `mglaman/phpstan-drupal` (+ règles de dépréciation).
- Rapport de **couverture** sur Unit/Kernel (objectif ≥ 85 % sur `src/Domain/` et `src/Service/`).

---

## 5. Vérification (end-to-end)

1. **À chaque phase** : `composer install` puis `drush en mespronos` sur un Drupal 11 neuf → activation sans erreur ; `vendor/bin/phpunit`, `phpstan`, `phpcs` verts en local et en CI.
2. **Parité scoring** (gate dur) : sur une **copie de la DB de prod** restaurée en staging, configurer la connexion `migrate`, puis :
   ```
   drush migrate:import --group=mespronos --feedback=100
   drush migrate:status --group=mespronos
   drush mespronos:recompute-rankings --all      # reconstruit la table d'agrégats
   drush mespronos:verify-points                 # diff recalculé vs hérité (paris) → doit être 0
   ```
   Itérer avec `drush migrate:rollback` jusqu'à `verify-points` à 100 %.
3. **Parité fonctionnelle** : parcourir les pages clés (classements, jour, ligue, profil, formulaires de pari et de saisie des scores, blocs podium/derniers paris) et comparer à la prod.
4. **Cutover prod** : mode maintenance → import delta final (highwater) → recompute → smoke tests → sortie de maintenance.

**Risques & mitigations** : perf du recompute sur le volume réel (Drush batché + queue) ; remapping uid/group-id (`migration_lookup` + `verify`) ; dérive de la règle de points (gate `verify-points`) ; courbe d'apprentissage contrib Group (gate de décision phase 8).

---

## 6. Fichiers de référence (lecture obligatoire avant implémentation)

- `src/Controller/BetController.php:41-61` — algorithme de points canonique à extraire dans `ScoringService`.
- `src/Entity/Base/RankingBase.php` — anti-modèle à supprimer : entité révisionnable stockant `points`, position calculée en PHP (`determinePosition()` l.29) en chargeant tout le classement (`getRanking()` l.63, `loadMultiple` + `usort`). Remplacé par la table d'agrégats + `RankingRepository`.
- `src/Form/GamesMarks.php:92-129` — orchestration score→points→classements→events→cache → `ResultService` + subscriber.
- `src/Service/NotificationManager.php` — god class 291 l. → 3 services dans `mespronos_notification`.
- `src/Entity/League.php` — règles de points, `betting_type`/`status`, `close()` → `PointRules`/enums + `LeagueLifecycleService` ; définit le schéma de champs que la migration doit mapper.

---

# 7. Tickets détaillés

Chaque phase laisse le module installable.

## Phase 1 — Scaffolding + double CI ([#121](https://github.com/mespronos/mespronos/issues/121))

**Objectif.** Poser une base D11 propre et installable, avec les deux pipelines de CI et la QA partagée, **avant** d'écrire du code métier.

**Tâches.**
- `composer.json` : `drupal/core ^11`, `php >=8.4`, deps dev (`drupal/coder`, `mglaman/phpstan-drupal`, `phpunit`).
- `mespronos.info.yml` : `core_version_requirement: ^11`, suppression de `core: 8.x`.
- `mespronos.services.yml` vide (prêt à recevoir les services).
- Configs QA racine **partagées** : `phpstan.neon` (niveau **5**), `phpcs.xml.dist` (**Drupal + DrupalPractice**), `phpunit.xml.dist`.
- **CI Drupal.org (GitLab)** : `.gitlab-ci.yml` basé sur `drupal/gitlab-templates` (`phpcs`, `phpstan`, `phpunit`, `composer-lint`).
- **GitHub Actions** : `.github/workflows/ci.yml` équivalent (matrice PHP 8.3/8.4 + D11.x, service MariaDB).

**Critères d'acceptation.** `composer install` OK ; `drush en mespronos` sur un D11 neuf sans erreur ; phpcs/phpstan verts en local et sur **les deux** CI ; les deux pipelines exécutent réellement les tests.

## Phase 2 — Primitives domaine + ScoringService (tests unitaires) ([#122](https://github.com/mespronos/mespronos/issues/122))

**Objectif.** Verrouiller **en premier** la règle de points dans une classe pure, testée unitairement, avant toute dépendance.

**Tâches.**
- Enums `BettingType`, `LeagueStatus`, `MatchOutcome` (remplacent les tableaux statiques de `League`).
- Value objects `readonly` : `Domain\Score(team1, team2)` avec `winner()`, `Domain\PointRules(scoreFound, winnerFound, participation)`.
- `Service\ScoringService` : algorithme extrait **tel quel** de `src/Controller/BetController.php:41-61`. **Pur**, sans `\Drupal::`, sans I/O.
- `tests/src/Unit/ScoringServiceTest` couvrant tous les paliers + cas limites du legacy `MespronosBetPointsTest`.
- Tests unitaires des value objects/enums.

**Critères d'acceptation.** `ScoringServiceTest` vert et exhaustif ; aucune dépendance Drupal dans `ScoringService`.

## Phase 3 — Entités D11 (`#[ContentEntityType]`, data-only) ([#123](https://github.com/mespronos/mespronos/issues/123))

**Objectif.** Définir les **7 entités de contenu** en attributs PHP D11, **data-only**.

**Périmètre.** 7 entités conservées : Sport, Team, League, Day, Game, Bet, Reminder. 3 entités supprimées (`RankingDay/League/General`) → table d'agrégats `mespronos_ranking_points` (cf. #124/#125).

**Tâches.**
- Les 7 entités en `#[ContentEntityType(...)]`.
- `baseFieldDefinitions()`, getters/setters typés, `preCreate` pour l'ownership.
- Handlers via la map `handlers` ; `MPNContentEntityBase` base fine.
- Intégrer enums/value objects comme wrappers sur les **mêmes colonnes** (mappables 1:1).
- Renommer `isScoreSetted()` → `isScoreSet()`.
- Tables `mespronos_<entity>`.
- **Vérifier la liste des champs contre le legacy** (`src/Entity/League.php`).
- Schéma de `mespronos_ranking_points(scope_type, scope_id, uid, points, games_betted)` (`hook_schema`), index `(scope_type, scope_id, points)`.

**Critères d'acceptation.** Module installable, schéma créé sans erreur (7 entités + table d'agrégats) ; entités sans requête/`\Drupal::`/HTML ; aucune entité de classement.

## Phase 4 — Repositories + tests Kernel ([#124](https://github.com/mespronos/mespronos/issues/124))

**Objectif.** Sortir **toutes** les requêtes des méthodes statiques d'entités vers des repositories injectables.

**Tâches.**
- `BetRepository`, `GameRepository`, `DayRepository`, `LeagueRepository` (corrige le bug de storage `game`→`league`).
- `RankingRepository` sur la table d'agrégats :
  - lecture avec **position calculée en SQL** (`RANK() OVER (ORDER BY points DESC)`, ex æquo natifs) + pagination ; plus de `loadMultiple` + `usort` ni `determinePosition()` ;
  - écriture **incrémentale par delta** (`applyDelta(scopeType, scopeId, uid, dPoints, dGames)`) et `rebuild()` complet ;
  - filtrage groupe/`bet_private` centralisé dans `applyGroupScope()`.
- Repositories renvoyant entités/DTO typés, jamais de render arrays.
- `MespronosKernelTestBase` (fixtures Sport→…→Bet).

**Tests (Kernel).** `BetRepositoryKernelTest` ; `RankingRepositoryKernelTest` (position via fonction fenêtre dont ex æquo, `applyDelta`, `rebuild`, `applyGroupScope`).

**Critères d'acceptation.** Plus aucune méthode statique de requête sur les entités ; aucune position en PHP ; tests Kernel verts.

## Phase 5 — Services domaine + traitement par lots/queue (montée en charge) ([#125](https://github.com/mespronos/mespronos/issues/125))

**Objectif.** Porter la logique métier dans des services injectables, et **traiter la montée en charge** du re-scoring + mise à jour des classements.

**Services.**
- `BetPlacementService` (pose/validation, deadlines).
- `RankingService` : **deltas incrémentaux** sur la table d'agrégats + `rebuild()` ; dispatch `RankingsRecomputedEvent` ; **aucune position stockée** ; calcul du delta = méthode **pure unit-testable**.
- `ResultService` : orchestration score→re-scoring→agrégats.
- `LeagueLifecycleService`, `StatisticsService`, `DomainResolver`.
- Event subscriber d'invalidation de cache.

**Montée en charge.** Périmètre **borné** (paris du match + 3 lignes d'agrégat des auteurs via delta, position non recalculée) ; **aucun chargement intégral en mémoire** (chunks + `resetCache`) ; `ScoringService` pur ; **Batch API** (admin), **Queue API + worker cron** (asynchrone).

**Tests.** Kernel (`RankingServiceKernelTest`, `BetPlacementServiceKernelTest`, `LeagueLifecycleKernelTest`) ; Unit (calcul de delta, orchestration par lots) ; **`ScoringScaleKernelTest`** (5 000+ paris).

**Critères d'acceptation.** Saisie de score sur gros volume sans timeout ni explosion mémoire ; aucune position stockée ; tests Kernel + charge verts.

## Phase 6 — Présentation + front (SDC) ([#126](https://github.com/mespronos/mespronos/issues/126))

**Objectif.** Couche présentation fine : controllers/blocs/formulaires délèguent aux services, front migré en SDC.

**Tâches.** Controllers fins (purger Day/RankingController) ; blocs sur repositories (supprimer `new LastBetsController()`) ; formulaires délégant aux services (plus de `createRanking()`/`Cache::invalidateTags()` manuels) ; SDC sous `components/` ; view builders via `#type: component` ; remplacer `render()`/`\Drupal::l()` par `#type link`/`Url`.

**Tests (Functional).** `GamesBettingFormTest`, `GamesMarksFormTest`, `RankingPageTest`, `DayPageTest`.

**Critères d'acceptation.** Plus de HTML en dur ni `\Drupal::l()`/`render()` en présentation ; aucune logique métier dans formulaires/blocs ; tests fonctionnels verts.

## Phase 7 — Sous-module mespronos_notification ([#127](https://github.com/mespronos/mespronos/issues/127))

**Objectif.** Décomposer la god class `NotificationManager` (291 l.).

**Tâches.** `ReminderScheduler` (cron, config, garde déjà-envoyé/state) ; `ReminderRecipientResolver` (qui notifier) ; `ReminderMailer` (rendu + envoi, **timezone via config**) ; `hook_mail` + cron ; full DI, suppression du SQL brut.

**Tests.** Unit (`ReminderRecipientResolver`) ; Kernel (`ReminderSchedulerKernelTest`).

**Critères d'acceptation.** `NotificationManager` supprimé, remplacé par 3 services testés ; sous-module activable/désactivable indépendamment.

## Phase 8 — Sous-module mespronos_group (drupal/group 3.x) — *gate de décision* ([#128](https://github.com/mespronos/mespronos/issues/128))

**Objectif.** Remplacer l'entité Group custom (~700 l.) par un **adaptateur fin** au-dessus de `drupal/group` 3.x. **Gate de décision** avec le commanditaire avant d'engager.

**Tâches.** `GroupType` « mespronos_group » + plugin de relation ; **classements par groupe** via `RankingRepository::applyGroupScope()` ; accès inscription + mail « nouveau groupe » ; **repli documenté** (entité Group custom minimale).

**Tests (Functional).** `GroupRankingTest`.

**Critères d'acceptation.** Adhésion + classement par groupe fonctionnels via `drupal/group` ; membership/join/leave/accès fournis par contrib.

## Phase 9 — Sous-module mespronos_import ([#129](https://github.com/mespronos/mespronos/issues/129))

**Objectif.** Porter l'import (`FormImport`/`ImporterController`) en service propre.

**Tâches.** Logique d'import dans un service injectable (full DI) ; formulaire fin déléguant au service ; suppression du SQL brut / APIs dépréciées.

**Tests.** Kernel/Functional selon faisabilité des fixtures.

**Critères d'acceptation.** Import fonctionnel via le service ; sous-module activable indépendamment.

## Phase 10 — Sous-module mespronos_migrate + Drush recompute/verify ([#130](https://github.com/mespronos/mespronos/issues/130))

**Objectif.** Migrer les données de prod via l'API Migrate + commandes recompute/verify.

**Tâches.** Plugins `SqlBase` par table legacy (connexion `migrate`) ; destinations `entity:<id>` + `migration_lookup` + process plugin enums ; configs `migrate_plus.migration.mespronos_*.yml` dans l'ordre `sport → team → league → day → game → bet`, `reminder` après `day` ; **classements recalculés, pas migrés** ; Drush `mespronos:recompute-rankings --all` (rebuild de la table d'agrégats, batché) ; Drush `mespronos:verify-points` (diff vs `points` des paris).

**Critères d'acceptation.** `migrate:import` importe les 7 entités dans le bon ordre ; `verify-points` → **0 diff** sur copie prod ; `migrate:rollback` réversible.

## Phase 11 — Durcissement tests / CI ([#131](https://github.com/mespronos/mespronos/issues/131))

**Objectif.** Rendre les deux pipelines vertes et durcir couverture/qualité.

**Tâches.** PHPUnit (Unit + Kernel + Functional) vert sur PHP 8.3/8.4 + D11.x ; phpcs `Drupal`/`DrupalPractice` sans erreur ; phpstan **niveau 5** sans erreur ; **couverture ≥ 85 %** sur `src/Domain/` et `src/Service/` ; suppression des **15 fichiers `src/Tests/*`** legacy ; parité de règles entre GitLab et GitHub Actions.

**Critères d'acceptation.** Les **deux** CI vertes ; couverture ≥ 85 % ; plus aucun test legacy.

## Phase 12 — Cutover prod ([#132](https://github.com/mespronos/mespronos/issues/132))

**Objectif.** Basculer la prod après validation de parité.

**Déroulé.** Parité scoring (gate dur) sur copie de la DB de prod (`migrate:import` → `recompute-rankings --all` → `verify-points` = 0, itérer avec `migrate:rollback`) ; parité fonctionnelle (pages clés vs prod) ; **cutover** : maintenance → import delta final (highwater) → recompute → smoke tests → sortie de maintenance.

**Critères d'acceptation.** `verify-points` → 0 diff ; parité fonctionnelle validée ; bascule sans perte de données.
