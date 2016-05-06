<?php

/**
 * @file
 * Contains Drupal\mespronos\Controller\BetController.
 */

namespace Drupal\mespronos\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\mespronos\Entity\Day;
use Drupal\mespronos\Entity\League;
use Drupal\mespronos\Entity\RankingDay;
use Drupal\mespronos\Entity\RankingLeague;
use Drupal\mespronos\Entity\RankingGeneral;
use Drupal\mespronos\Entity\Game;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\Query\Condition;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Cache\Cache;
use Drupal\user\Entity\User;

/**
 * Provides a list controller for Game entity.
 *
 * @ingroup mespronos
 */
class BetController extends ControllerBase {

  /**
   * Define bets scores for a given game
   * @param \Drupal\mespronos\Entity\Game $game
   * @return boolean
   */
  public static function updateBetsFromGame(Game $game) {
    $injected_database = Database::getConnection();
    if(!$game->isScoreSetted()) {
      $query = $injected_database->update('mespronos__bet');
      $query->fields(['points'=>null,'changed'=>time()]);
      $query->condition('game',$game->id());
      $query->execute();
      unset($query);
      return false;
    }
    $st1 = $game->getScoreTeam1();
    $st2 = $game->getScoreTeam2();
    $points = $game->getLeague()->getPoints();

    //perfect bet
    $query = $injected_database->update('mespronos__bet');
    $query->fields(['points'=>$points['points_score_found'],'changed'=>time()]);
    $query->condition('score_team_1',$st1);
    $query->condition('score_team_2',$st2);
    $query->condition('game',$game->id());
    $query->execute();
    unset($query);

    if($st1 == $st2) {
      $query = $injected_database->update('mespronos__bet');
      $query->fields(['points'=>$points['points_winner_found'],'changed'=>time()]);
      $query->where('score_team_1 = score_team_2');
      $query->condition('score_team_2',$st2,'!=');
      $query->condition('score_team_1',$st1,'!=');
      $query->condition('game',$game->id());
      $query->execute();
      unset($query);

      $query = $injected_database->update('mespronos__bet');
      $query->fields(['points'=>$points['points_participation'],'changed'=>time()]);
      $query->where('score_team_1 <> score_team_2');
      $query->condition('game',$game->id());
      $query->execute();
      unset($query);
    }
    else {
      $query = $injected_database->update('mespronos__bet');
      $query->fields(['points'=>$points['points_participation'],'changed'=>time()]);
      $query->where('score_team_1 = score_team_2');
      $query->condition('game',$game->id());
      $query->execute();
      unset($query);

      $notExactScore = new Condition('OR');
      $notExactScore->condition('score_team_2',$st2,'!=');
      $notExactScore->condition('score_team_1',$st1,'!=');

      if($st1 > $st2) {
        $query = $injected_database->update('mespronos__bet');
        $query->fields(['points'=>$points['points_winner_found'],'changed'=>time()]);
        $query->where('score_team_1 > score_team_2');
        $query->condition($notExactScore);
        $query->condition('game',$game->id());
        $query->execute();
        unset($query);

        $query = $injected_database->update('mespronos__bet');
        $query->fields(['points'=>$points['points_participation'],'changed'=>time()]);
        $query->where('score_team_1 < score_team_2');
        $query->condition('game',$game->id());
        $query->execute();
        unset($query);
      }

      if($st1 < $st2) {
        $query = $injected_database->update('mespronos__bet');
        $query->fields(['points'=>$points['points_winner_found'],'changed'=>time()]);
        $query->where('score_team_1 < score_team_2');
        $query->condition($notExactScore);
        $query->condition('game',$game->id());
        $query->execute();
        unset($query);

        $query = $injected_database->update('mespronos__bet');
        $query->fields(['points'=>$points['points_participation'],'changed'=>time()]);
        $query->where('score_team_1 > score_team_2');
        $query->condition('game',$game->id());
        $query->execute();
        unset($query);
      }
    }
    return true;
  }

  public static function updateBetsForDay(Day $day) {
    $games = $day->getGames();

    foreach ($games as $game) {
      self::updateBetsFromGame($game);
    }
    drupal_set_message(t('Points updated for @nb games',['@nb'=>count($games)]));
    $response = RankingController::recalculateDay($day);
    return $response;
  }

  public static function updateBetsForLeague(League $league) {
    $games = $league->getGames();
    $nb_game_updated = 0;
    $days_to_update = [];
    foreach ($games as $game) {
      if(self::updateBetsFromGame($game)) {
        $nb_game_updated++;
        if(!isset($days_to_update[$game->getDayId()])) {
          $days_to_update[$game->getDayId()] = $game->getDay();
        }
      }

    }
    $nb_updates = 0;
    foreach($days_to_update as $day) {
      $nb_updates += RankingDay::createRanking($day);
    }
    RankingLeague::createRanking($league);
    RankingGeneral::createRanking();
    Cache::invalidateTags(array('ranking'));

    drupal_set_message(t('Points updated for @nb games',['@nb'=>$nb_game_updated]));
    drupal_set_message(t('Ranking updated for @nb days',['@nb'=>count($days_to_update)]));
    return new RedirectResponse(\Drupal::url('entity.league.collection'));
  }

  public static function getLastUserBetsTable(User $user,$nb_bets = 20, Day $day = null) {
    $bets = self::getLastUserBets($user,$nb_bets);
    $rows = [];
    $leagues = [];
    foreach($bets as $bet) {
      $game = $bet->getGame(true);
      $day = $game->getDay();
      if(!isset($leagues[$day->getLeagueID()])) {
        $leagues[$day->getLeagueID()] = $day->getLeague();
      }

      $day_renderable = $day->getRenderableLabel();
      $row = [
        'data' => [
          'day' => [
            'data' => render($day_renderable),
            'class' => ['day-cell']
          ],
          $game->labelTeams(),
          $game->labelScore(),
          $bet->Label(),
          $bet->getPoints(),
        ],
        'class' => $leagues[$day->getLeagueID()]->getPointsCssClass($bet->getPoints()),
      ];

      $rows[] = $row;
    }

    $header = [
      t('League',array(),array('context'=>'mespronos')),
      t('Game',array(),array('context'=>'mespronos')),
      t('Score',array(),array('context'=>'mespronos')),
      t('Bet',array(),array('context'=>'mespronos')),
      t('Points',array(),array('context'=>'mespronos')),
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

    return $table_array;
  }

  /**
   * @param \Drupal\user\Entity\User $user
   * @param int $nb_bets
   * @return \Drupal\mespronos\Entity\Bet[]
   */
  public static function getLastUserBets(User $user,$nb_bets = 20) {
    $bet_storage = \Drupal::entityManager()->getStorage('bet');
    $ids = \Drupal::entityQuery('bet')
      ->condition('better',$user->id())
      ->condition('points','','IS NOT NULL')
      ->sort('created','DESC')
      ->range(0,$nb_bets)
      ->execute();
    if(count($ids)>0) {
      return $bet_storage->loadMultiple($ids);
    }
    return [];
  }

  /**
   * @param \Drupal\user\Entity\User $user
   * @param \Drupal\mespronos\Entity\Game $game
   * @return \Drupal\mespronos\Entity\Bet
   */
  public static function loadForUser(User $user,Game $game) {

    $bet_storage = \Drupal::entityManager()->getStorage('bet');

    $ids = \Drupal::entityQuery('bet')
      ->condition('game',$game->id())
      ->condition('better',$user->id())
      ->execute();
    if(count($ids)>0) {
      return $bet_storage->load(array_pop($ids));
    }
    else {
      return $bet_storage->create(array());
    }
  }

  /**
   * Determine number of games left to bet for a given user on a given day
   * @param \Drupal\user\Entity\User $user
   * @param \Drupal\mespronos\Entity\Day $day
   * @return integer number of game left to bet
   */
  public static function betsLeft(\Drupal\user\Entity\User $user,Day $day) {
    $now_date = new \DateTime();
    $now_date->setTimezone(new \DateTimeZone("GMT"));

    $injected_database = Database::getConnection();

    $subquery = $injected_database->select('mespronos__bet','b');
    $subquery->leftJoin('mespronos__game','g','b.game = g.id');
    $subquery->fields('g',['id']);
    $subquery->condition('g.day',$day->id());
    $subquery->condition('b.better',$user->id());

    $query = $injected_database->select('mespronos__game','g');
    $query->addExpression('count(g.id)','nb_bet_left');
    $query->condition('g.day',$day->id());
    $query->condition('g.game_date',$now_date->format('Y-m-d\TH:i:s'),'>');
    $query->condition('g.id', $subquery, 'NOT IN');

    $results = $query->execute()->fetchAssoc();
    return $results['nb_bet_left'];
  }

}
