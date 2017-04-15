<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\Game.
 */

namespace Drupal\mespronos\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\mespronos\MPNEntityInterface;
use Drupal\mespronos\Controller\BetController;

/**
 * Defines the Game entity.
 *
 * @ingroup mespronos
 *
 * @ContentEntityType(
 *   id = "game",
 *   label = @Translation("Game entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\mespronos\Entity\Controller\GameListController",
 *     "views_data" = "Drupal\mespronos\Entity\ViewsData\GameViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\mespronos\Entity\Form\GameForm",
 *       "add" = "Drupal\mespronos\Entity\Form\GameForm",
 *       "edit" = "Drupal\mespronos\Entity\Form\GameForm",
 *       "delete" = "Drupal\mespronos\Entity\Form\MPNDeleteForm",
 *       "remove_bets" = "Drupal\mespronos\Entity\Form\GameRemoveBetsForm",
 *     },
 *     "access" = "Drupal\mespronos\ControlHandler\GameAccessControlHandler",
 *   },
 *   base_table = "mespronos__game",
 *   admin_permission = "administer Game entity",
 *   fieldable = FALSE,
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/mespronos/game/{group}",
 *     "edit-form" = "/admin/game/{game}/edit",
 *     "delete-form" = "/admin/game/{game}/delete",
 *     "remove-bets" = "/admin/game/{game}/remove-bets"
 *   },
 *   field_ui_base_route = "game.settings"
 * )
 */
class Game extends MPNContentEntityBase implements MPNEntityInterface {
  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += array(
      'user_id' => \Drupal::currentUser()->id(),
    );
  }

  public function save() {
    $return = parent::save();
    if ($this->isScoreSetted()) {
      BetController::updateBetsFromGame($this);
    }
    return $return;
  }

  public function label() {
    $league = $this->getLeague();
    $day = $this->getDay();
    $team1 = $this->getTeam1();
    $team2 = $this->getTeam2();

    return t('@team1 - @team2 (@league - @day)', [
      '@team1'=> $team1->label(),
      '@team2'=> $team2->label(),
      '@league'=>$league->label(),
      '@day' => $day->label()
    ]);
  }

  public function labelTeams() {
    $team1 = $this->getTeam1();
    $team2 = $this->getTeam2();
    return t('@team1 - @team2', array('@team1'=> $team1->label(), '@team2'=> $team2->label()));
  }

  public function labelTeamsAndHour() {
    $team1 = $this->getTeam1();
    $team2 = $this->getTeam2();
    $date = new \DateTime($this->getGameDate(), new \DateTimeZone('UTC'));
    $date->setTimezone(new \DateTimeZone("Europe/Paris"));


    return [
      '#theme' => 'game-with-flag',
      '#team_1' => [
        'label' => $team1->label(),
        'logo' => $team1->getLogo('mini_logo'),
      ],
      '#team_2' => [
        'label' => $team2->label(),
        'logo' => $team2->getLogo('mini_logo'),
      ],
      '#game' => [
        'date' => $date->format('d/m/Y H\hi'),
      ],
    ];

  }

  public function labelScore() {
    return t('@t1 - @t2', array('@t1'=> $this->get('score_team_1')->value, '@t2'=> $this->get('score_team_2')->value));
  }

  public function labelWithScore() {
    $team1 = $this->getTeam1();
    $team2 = $this->getTeam2();
    return t('<span class="team team-1">@team1</span> <span class="score">@s1 - @s2</span> <span class="team team-2">@team2</span>', array('@team1'=> $team1->label(), '@team2'=> $team2->label(), '@s1'=> $this->get('score_team_1')->value, '@s2'=> $this->get('score_team_2')->value));
  }

  public function labelForInsight() {
    $team1 = $this->getTeam1();
    $team2 = $this->getTeam2();
    $league = $this->getLeague();
    $date = new \DateTime($this->getGameDate(), new \DateTimeZone('UTC'));
    $date->setTimezone(new \DateTimeZone("Europe/Paris"));
    return t('@team1 @s1 - @s2 @team2 (@league - @date)', array(
      '@team1'=> $team1->label(),
      '@team2'=> $team2->label(),
      '@s1'=> $this->get('score_team_1')->value,
      '@s2'=> $this->get('score_team_2')->value,
      '@league'=> $league->getTheName(),
      '@date'=> $date->format('d/m/Y'),
      )
    );
  }

  public function label_full() {
    $team1 = $this->getTeam1();
    $team2 = $this->getTeam2();
    $date = new \DateTime($this->getGameDate(), new \DateTimeZone('UTC'));
    $date->setTimezone(new \DateTimeZone("Europe/Paris"));
    return t('@team1 - @team2 - @date', array('@team1'=> $team1->label(), '@team2'=> $team2->label(), '@date'=> $date->format('d/m/Y H\hi')));
  }


  public function labelDate() {
    $date = new \DateTime($this->getGameDate(), new \DateTimeZone('UTC'));
    $date->setTimezone(new \DateTimeZone("Europe/Paris"));
    return\Drupal::service('date.formatter')->format($date->format('U'), 'long');
  }

  public function isPassed() {
    $game_date = \DateTime::createFromFormat('Y-m-d\TH:i:s', $this->getGameDate(), new \DateTimeZone("GMT"));
    $game_date->setTimezone(new \DateTimeZone("Europe/Paris"));
    $now = new \DateTime(null, new \DateTimeZone("UTC"));
    return($game_date < $now);
  }

  public static function getGamesForDay(Day $day) {
    $game_storage = \Drupal::entityManager()->getStorage('game');
    $query = \Drupal::entityQuery('game');
    $query->condition('day', $day->id());
    $query->sort('game_date', 'ASC');
    $query->sort('id', 'ASC');
    $ids = $query->execute();
    $return = [
      'ids' => $ids,
      'entities' => $game_storage->loadMultiple($ids),
    ];
    return $return;
  }

  /**
   * @param $number
   * @return \Drupal\mespronos\Entity\Game[]
   */
  public static function getLastestGamesWithMark($number) {
    $game_storage = \Drupal::entityManager()->getStorage('game');
    $query = \Drupal::entityQuery('game');
    $query->condition('score_team_1', 0, '>=');
    $query->condition('score_team_2', 0, '>=');
    $query->sort('game_date', 'DESC');
    $query->sort('id', 'ASC');
    $query->range(0, $number);
    $ids = $query->execute();
    $return = $game_storage->loadMultiple($ids);
    return $return;

  }
  /**
   * Remove bets on current day
   *
   * @return integer number of deleted bets
   */
  public function removeBets() {
    $storage = \Drupal::entityManager()->getStorage('bet');
    $query = \Drupal::entityQuery('bet');
    $query->condition('game', $this->id());
    $ids = $query->execute();
    $bets = $storage->loadMultiple($ids);
    foreach ($bets as $bet) {
      $bet->delete();
    }
    \Drupal::logger('mespronos')->notice(t('Bets removed on game #@id (@game_label) : @nb_bets removed', [
      '@id'=>$this->id(),
      '@game_label'=>$this->label(),
      '@nb_bets' => count($ids),
    ]));
    return count($ids);
  }

  /**
   * Return number of games on current day
   *
   * @return integer nb bets for given game
   */
  public function getNbBets() {
    $query = \Drupal::entityQuery('bet');
    $query->condition('game', $this->id());
    $ids = $query->execute();
    return count($ids);
  }
  /**
   * @return bool
   */
  public function isScoreSetted() {
    return !is_null($this->getScoreTeam1()) && !is_null($this->getScoreTeam1());
  }

  public function getWinner() {
    if(!$this->isScoreSetted()) {
      return false;
    }
    if($this->getScoreTeam1() > $this->getScoreTeam2()) {
      return 1;
    } elseif($this->getScoreTeam1() < $this->getScoreTeam2()) {
      return 2;
    } else {
      return 'N';
    }
  }

  /**
   * @return integer
   */
  public function getScoreTeam1() {
    return $this->get('score_team_1')->value;
  }

  /**
   * @return integer
   */
  public function getScoreTeam2() {
    return $this->get('score_team_2')->value;
  }

  public function getGameDate() {
    return $this->get('game_date')->value;
  }

  /**
   * Return Team1 id
   * @return integer
   */
  public function getTeam1Id() {
    return $this->get('team_1')->target_id;
  }

  /**
   * Return Team1 entity
   * @return Team
   */
  public function getTeam1() {
    $team_storage = \Drupal::entityManager()->getStorage('team');
    $team = $team_storage->load($this->getTeam1Id());
    return $team;
  }

  /**
   * Return Team2 id
   * @return integer
   */
  public function getTeam2Id() {
    return $this->get('team_2')->target_id;
  }

  /**
   * Return Team2 entity
   * @return Team
   */
  public function getTeam2() {
    $team_storage = \Drupal::entityManager()->getStorage('team');
    $team = $team_storage->load($this->getTeam2Id());
    return $team;
  }


  /**
   * @return League
   */
  public function getLeague() {
    $day_storage = \Drupal::entityManager()->getStorage('day');
    $league_storage = \Drupal::entityManager()->getStorage('league');
    $day = $day_storage->load($this->get('day')->target_id);
    $league = $league_storage->load($day->get('league')->target_id);
    return $league;
  }

  /**
   * Return game's day entity
   * @return Day
   */
  public function getDay() {
    $day_storage = \Drupal::entityManager()->getStorage('day');
    $day = $day_storage->load($this->get('day')->target_id);
    return $day;
  }

  /**
   * Return game's day id
   * @return integer
   */
  public function getDayId() {
    return $this->get('day')->target_id;
  }

  public function setScore($score_team_1, $score_team_2) {
    $this->set('score_team_1', $score_team_1);
    $this->set('score_team_2', $score_team_2);
    return $this;
  }

  public function getBaseTable() {
    return 'mespronos__game';
  }

  public function getDataTable() {
    return 'mespronos__game';
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['creator'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of the Game entity author.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDefaultValueCallback('Drupal\node\Entity\Node::getCurrentUserId')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'author',
        'weight' => -2,
      ))
      ->setDisplayConfigurable('form', false)
      ->setDisplayConfigurable('view', false);

    $fields['day'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Day'))
      ->setDescription(t('Day entity reference'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'day')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'entity_reference',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'options_select',
        'weight' => -1,
        'settings' => array(),
      ))
      ->setDisplayConfigurable('form', false)
      ->setDisplayConfigurable('view', TRUE);

    $fields['team_1'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Receving Team'))
      ->setDescription(t('Hosting team'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'team')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'entity_reference',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'options_select',
        'weight' => 2,
        'settings' => array(),
      ))
      ->setDisplayConfigurable('form', false)
      ->setDisplayConfigurable('view', TRUE);

    $fields['team_2'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Guest Team'))
      ->setDescription(t('Second team'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'team')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'entity_reference',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'options_select',
        'weight' => 3,
        'settings' => array(),
      ))
      ->setDisplayConfigurable('form', false)
      ->setDisplayConfigurable('view', TRUE);

    $fields['score_team_1'] = BaseFieldDefinition::create('integer')
      ->setLabel('Score Team 1')
      ->setRevisionable(TRUE)
      ->setSetting('unsigned', TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'weight' => 4,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'number',
        'weight' => 4,
      ));

    $fields['score_team_2'] = BaseFieldDefinition::create('integer')
      ->setLabel('Score Team 2')
      ->setRevisionable(TRUE)
      ->setSetting('unsigned', TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'weight' => 5,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'number',
        'weight' => 5,
      ));

    $fields['game_date'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('Date'))
      ->setDescription(t('The game\'s date'))
      ->setSettings(array(
        'default_value' => '',
        'max_length' => 50,
        'text_processing' => 0,
      ))
      ->setDefaultValue(array(0 => array(
        'default_date_type' => 'now',
        'default_date' => 'now',
      )))
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'datetime_default',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'datetime_default',
        'weight' => 1,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);



    return $fields;
  }

}
