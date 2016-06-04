<?php

/**
 * @file
 * Contains \Drupal\mespronos\Controller\DayController.
 */

namespace Drupal\mespronos\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\mespronos\Entity\Day;
use Drupal\mespronos\Entity\Game;
use Drupal\mespronos\Entity\Bet;
use Drupal\mespronos\Entity\League;
use Drupal\mespronos_group\Entity\Group;
use Drupal\user\Entity\User;
use Drupal\Core\Database\Database;

/**
 * Class DayController.
 *
 * @package Drupal\mespronos\Controller
 */
class DayController extends ControllerBase {

  public function index(Day $day, User $user = null) {
    $groups = false;
    if($user == null || $user->id() == \Drupal::currentUser()->id()) {
      $user = User::load(\Drupal::currentUser()->id());
      $groups = $this->getGroup($user);
    }
    $rows = $this->getDayRows($day,$user);

    $groups_ranking = [];
    if($groups) {
      foreach ($groups as $group) {
        $groups_ranking[] = [
          'label' => $group->label(),
          'group_ranking' => RankingController::getRankingTableForDay($day,$group),
        ];
      }
    }

    $header = [
      $this->t('Game',array(),array('context'=>'mespronos')),
      $this->t('Score',array(),array('context'=>'mespronos')),
      $this->t('Bet',array(),array('context'=>'mespronos')),
      $this->t('Points',array(),array('context'=>'mespronos')),
    ];

    $table_array = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
      '#cache' => [
        'contexts' => ['user'],
        'tags' => [ 'lastbets','user:'.$user->id()],
      ],
    ];
    return [
      '#theme' =>'day-details',
      '#last_bets' => $table_array,
      '#ranking' => RankingController::getRankingTableForDay($day),
      '#groups' => $groups_ranking,
      '#cache' => [
        'contexts' => ['user'],
        'tags' => [ 'user:'.\Drupal::currentUser()->id().'_'.$user->id(),'lastbets'],
      ],
    ];
  }

  /**
   * @param \Drupal\user\Entity\User|NULL $user
   * @return bool|\Drupal\mespronos_group\Entity\Group[]
   */
  private function getGroup(User $user = null) {
    if($user != null && \Drupal::moduleHandler()->moduleExists('mespronos_group')) {
      $groups = Group::getUserGroup($user);
    }
    else {
      $groups = false;
    }
    return $groups;
  }

  private function getDayRows(Day $day, User $user) {
    $games = Game::getGamesForDay($day);
    $games_ids = $games['ids'];
    $games_entity = $games['entities'];
    $bets = Bet::getUserBetsForGames($games_ids,$user);
    $league = $day->getLeague();
    $rows = [];
    foreach($games_entity as $gid => $game) {
      if($user->id() !== \Drupal::currentUser()->id() && !$game->isPassed()) {
        $bet = '?';
      }
      else {
        $bet = isset($bets[$gid]) ? $bets[$gid]->labelBet() : '/';
      }
      $points = isset($bets[$gid]) ? $bets[$gid]->get('points')->value : '/';

      $row = [
        'data' => [
          $game->labelTeams(),
          $game->labelScore(),
          $bet,
          $points,
        ],
        'class' => $league->getPointsCssClass($points),
      ];

      $rows[] = $row;
    }
    return $rows;
  }

  public function indexTitle(Day $day, \Drupal\user\Entity\User $user = null) {
    $league = $day->getLeague();
    if($user == null || $user->id() == \Drupal::currentUser()->id()) {
      return t('My bets on @day',array('@day'=>$league->label().' - '.$day->label()));
    }
    else {
      return t('@user\'s bets on @day',array('@day'=>$league->label().' - '.$day->label(),'@user'=>$user->getUsername()));
    }

  }

  /**
   * Return next days to bet on
   * @param int $nb number of days to return
   * @param \Drupal\mespronos\Entity\League|NULL $league
   * @return array of day
   */
  public static function getNextDaysToBet($nb = 5,League $league = null) {
    $day_storage = \Drupal::entityManager()->getStorage('day');
    $injected_database = Database::getConnection();
    $now = new \DateTime(null, new \DateTimeZone("UTC"));

    $query = $injected_database->select('mespronos__game','g');
    $query->addExpression('min(game_date)','day_date');
    $query->addExpression('count(g.id)','nb_game_left');
    $query->groupBy('day');
    $query->fields('g',array('day'));
    if($league) {
      $query->join('mespronos__day','d','d.id = g.day');
      $query->condition('d.league',$league->id());
    }
    $query->condition('game_date',$now->format('Y-m-d\TH:i:s'),'>');
    $query->orderBy('day_date','ASC');
    $query->range(0,$nb);
    $results = $query->execute();
    $results = $results->fetchAllAssoc('day');
    $days = $day_storage->loadMultiple(array_keys($results));
    foreach($results as $key => &$day_data) {
      $day_data->nb_game = $days[$key]->getNbGame();
      $day_data->entity = $days[$key];
    }
    return $results;
  }

  /**
   * Return past days
   * @param int $nb number of days to return
   * @param \Drupal\mespronos\Entity\League|NULL $league
   * @return mixed
   */
  public static function getlastDays($nb = 5,League $league = null) {
    $day_storage = \Drupal::entityManager()->getStorage('day');
    $injected_database = Database::getConnection();
    $now = new \DateTime(null, new \DateTimeZone("UTC"));

    $query = $injected_database->select('mespronos__game','g');
    $query->addExpression('min(game_date)','day_date');
    $query->addExpression('count(g.id)','nb_game_over');
    $query->groupBy('day');
    $query->fields('g',array('day'));
    if($league) {
      $query->join('mespronos__day','d','d.id = g.day');
      $query->condition('d.league',$league->id());
    }
    $query->condition('game_date',$now->format('Y-m-d\TH:i:s'),'<');
    $query->orderBy('day_date','DESC');
    $query->range(0,$nb);
    $results = $query->execute();
    $results = $results->fetchAllAssoc('day');
    $days = $day_storage->loadMultiple(array_keys($results));

    foreach($results as $key => &$day_data) {
      $day_data->nb_game = $days[$key]->getNbGame();
      $day_data->nb_game_with_score = $days[$key]->getNbGameWIthScore();
      $day_data->entity = $days[$key];
    }
    return $results;
  }
  
}
