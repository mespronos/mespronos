<?php
namespace Drupal\mespronos\Service;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;
use Drupal\Core\Url;
use Drupal\image\Entity\ImageStyle;
use Drupal\mespronos\Entity\Day;
use Drupal\mespronos\Entity\League;
use Drupal\mespronos\Entity\Reminder;
use Drupal\user\Entity\User;

class NotificationManager {

  /** @var \Drupal\Core\Database\Connection  */
  protected $connection;

  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  public function cron() {
    $this->notificationNextBets();
  }

  public function notificationNextBets() {
    if (!$this->isEnabled()) {
      return FALSE;
    }
    $hours = $this->getHoursDefined();
    $upcommings_games = $this->getUpcomming($hours);

    $users = $this->getUserWithEnabledReminder();

    $user_to_remind = $this->getUsersToRemind($users, $upcommings_games);
    if (\count($user_to_remind) > 0) {
      $days = $this->getDaysFromGames($upcommings_games);
      foreach ($days as $day) {
        /** @var Day $day */
        $betters_on_league = $this->getBetterOnLeague($day->getLeagueID());
        $user_to_remind_on_this_day = $this->getUsersToRemindOnThisDay($user_to_remind, $betters_on_league);
        $nb_mail = $this->sendReminder($user_to_remind_on_this_day, $day);
        if ($nb_mail > 0) {
          $key = 'mespronos.reminder.day.' . $day->id() . '.lastSend';
          \Drupal::state()->set($key, date('U'));
          $reminder = Reminder::create(array(
            'day' => $day->id(),
            'emails_sended' => $nb_mail,
          ));
          $reminder->save();
          \Drupal::logger('mespronos_reminder')->info(t('Reminder sended for day #@id (@game_label) : @nb_mail mails sended (total of @nb_user member with reminder enabled)', [
            '@id'=>$day->id(),
            '@game_label'=>$day->label(),
            '@nb_mail' => $nb_mail,
            '@nb_user' => count($users),
          ]));
        }
      }
    }
    return TRUE;
  }

  public function isEnabled() {
    $config = \Drupal::config('mespronos.reminder');
    return $config->get('enabled') == true ? true : false;
  }

  public function getHoursDefined() {
    $config = \Drupal::config('mespronos.reminder');
    $hours = $config->get('hours');
    return $hours ?? 0;
  }

  public function getHoursGapDefined() {
    $config = \Drupal::config('mespronos.reminder');
    $hours = $config->get('hours_gap');
    return $hours ?? 99999999;
  }

  public function sendReminder(array $users_to_remind, Day $day) {
    $league = $day->getLeague();
    if (empty($users_to_remind)) {
      return FALSE;
    }
    $nb_mail = 0;
    foreach ($users_to_remind as $user_to_remind) {
      $nb_mail++;
      $user = User::load($user_to_remind);
      $mailManager = \Drupal::service('plugin.manager.mail');
      $params = [];

      $mail = $this->getReminderEmailVariables($user, $day);
      $mail['#config']['baseurl'] = Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString();
      $mail['#config']['name'] = \Drupal::config('system.site')->get('name');
      if ($domain = \Drupal::service('mespronos.domain_manager')->getUserMainDomain($user)) {
        if($name = \Drupal::config('domain.config.' . $domain->id() . '.system.site')->get('name')) {
          $mail['#config']['name'] = $name;
        }
        $mail['#config']['baseurl'] = $domain->buildUrl('/');
      }

      $params['message'] = $this->getReminderEmailRendered($mail);
      $params['subject'] = t('@sitename - Bet Reminder - @league - @day', [
        '@sitename' => $mail['#config']['name'],
        '@league' => $league->label(),
        '@day' => $day->label(),
      ]);

      $mailManager->mail('mespronos', 'reminder', $user->getEmail(), $user->getPreferredLangcode(), $params, NULL, TRUE);
    }
    return $nb_mail;
  }

  public function getReminderEmailVariables(User $user, Day $day) {
    $league = $day->getLeague();
    $games = $day->getGames(TRUE);
    $emailvars = [];
    $emailvars['#theme'] = 'bet-reminder';

    $emailvars['#user'] = [
      'name' => $user->getAccountName(),
      'myaccount' => Url::fromRoute('entity.user.edit_form', ['user' => $user->id()]),
    ];

    $emailvars['#day'] = [
      'label' => $league->label() . ' - ' . $day->label(),
      'games' => [],
      'bet_link' => substr(Url::fromRoute('mespronos.day.bet', ['day' => $day->id()])->toString(), 1),
    ];
    $style = ImageStyle::load('thumbnail');

    foreach ($games as $game) {
      $date = new \DateTime($game->getGameDate(), new \DateTimeZone('UTC'));
      $date->setTimezone(new \DateTimeZone('Europe/Paris'));
      $team1 = $game->getTeam1();
      $team2 = $game->getTeam2();
      $logo_team1 = $team1->getLogoAsFile();
      $logo_team2 = $team2->getLogoAsFile();

      $emailvars['#day']['games'][] = [
        'team1' => $team1->label(),
        'team1_logo' => $logo_team1 ? $style->buildUrl($logo_team1->getFileUri()) : FALSE,
        'team2' => $team2->label(),
        'team2_logo' => $logo_team2 ? $style->buildUrl($logo_team2->getFileUri()) : FALSE,
        'date' => \Drupal::service('date.formatter')->format($date->format('U'), 'date_longue_sans_annee'),
      ];
    };

    return $emailvars;
  }

  public function getReminderEmailRendered($variables) {
    $rederer = \Drupal::service('renderer');
    $rendered_email = $rederer->renderPlain($variables);
    return $rendered_email;
  }

  /**
   * Return all days that plays between now and $nb_hours;
   * @param int $nb_hours number of hours
   * @return \Drupal\mespronos\Entity\Game[]
   */
  public function getUpcomming($nb_hours) {
    $date_to = new \DateTime(null, new \DateTimeZone("UTC"));
    $date_to->add(new \DateInterval('PT'.intval($nb_hours).'H'));
    $now = new \DateTime(null, new \DateTimeZone("UTC"));

    $game_storage = \Drupal::entityTypeManager()->getStorage('game');
    $query = \Drupal::entityQuery('game');

    $query->condition('game_date', $now->format('Y-m-d\TH:i:s'), '>');
    $query->condition('game_date', $date_to->format('Y-m-d\TH:i:s'), '<=');

    $group = $query->orConditionGroup()
      ->condition('score_team_1', null, 'is')
      ->condition('score_team_2', null, 'is');

    $query->sort('game_date', 'ASC');
    $query->sort('id', 'ASC');

    $ids = $query->condition($group)->execute();

    $games = $game_storage->loadMultiple($ids);

    $this->checkIfReminderAlreadySended($games);
    if (count($games) > 0) {
      \Drupal::logger('mespronos_reminder')->debug(t('@nb_games games upcomming in the next @hour hours', [
        '@nb_games'=>count($games),
        '@hour'=>$nb_hours,
      ]));
    }

    return $games;

  }

  public function checkIfReminderAlreadySended(&$games) {
    $gap = $this->getHoursGapDefined();
    $days = [];
    foreach ($games as $key => $game) {
      $day_id = $game->getDayId();
      $cacheKey = 'mespronos.reminder.day.' . $day_id . '.lastSend';
      if(!empty($days[$day_id])) {
        unset($games[$key]);
      }
      elseif ($lastSend = (int) \Drupal::state()->get($cacheKey)) {
        if (($lastSend + $gap * 3600) > date('U')) {
          $days[$day_id] = TRUE;
          unset($games[$key]);
        }
      }
    }
  }

  public function getUserWithEnabledReminder() {
    $query = \Drupal::entityQuery('user')
      ->condition('status', 1)
      ->condition('field_reminder_enable.value', 1);
    $uids = $query->execute();
    return $uids;
  }

  public function getUsersToRemind($users, $upcommings_games) {
    $user_to_remind = [];
    foreach ($users as $user_id) {
      if ($this->doUserHasMissingBets($user_id, $upcommings_games)) {
        $user_to_remind[] = $user_id;
      }
    }
    return $user_to_remind;
  }

  public function getUsersToRemindOnThisDay($user_to_remind, $betters_on_league) {
    $user_to_remind_on_this_day = [];

    $user_connected_this_last_days = $this->getUserConnectedThisLast30Days();
    foreach ($user_to_remind as $user) {
      //if (in_array($user, $betters_on_league) || in_array($user, $user_connected_this_last_days)) {
      if (in_array($user, $betters_on_league)) {
        $user_to_remind_on_this_day[] = $user;
      }
    }
    return $user_to_remind_on_this_day;
  }

  /**
   * @param integer $user_id id of user
   * @return \Drupal\mespronos\Entity\Game[]
   */
  public function doUserHasMissingBets($user_id, $games) {
    if (count($games) == 0) {return false; }
    $games_id = array_map(function($a) {return $a->id(); },$games);
    $injected_database = Database::getConnection();

    $query = $injected_database->select('mespronos__bet', 'b');
    $query->addExpression('count(b.id)', 'nb_bets_done');
    $query->condition('b.game', $games_id, 'IN');
    $query->condition('b.better', $user_id);
    $results = $query->execute()->fetchAssoc();

    return $results['nb_bets_done'] < count($games_id);
  }

  public function getDaysFromGames($games) {
    $days = [];
    foreach ($games as $game) {
      $day_id = $game->getDayId();
      if (!isset($days[$day_id])) {
        $days[$day_id] = $game->getDay();
      }
    }
    return $days;
  }

  public function getBetterOnLeague($league_id) {
    $injected_database = Database::getConnection();
    $query = $injected_database->select('mespronos__ranking_league', 'rl');
    $query->fields('rl', ['better']);
    $query->condition('rl.league', $league_id);
    $results = $query->execute()->fetchAllKeyed(0, 0);
    return array_values($results);
  }

  public function getUserConnectedThisLast30Days() {
    $query = \Drupal::entityQuery('user');
    $limit = date('U') - 15 * 24 * 3600;
    $query->condition('access', $limit, '>');
    return $query->execute();
  }

}
