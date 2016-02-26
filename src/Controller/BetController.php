<?php

/**
 * @file
 * Contains Drupal\mespronos\Controller\BetController.
 */

namespace Drupal\mespronos\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\mespronos\Entity\Day;
use Drupal\mespronos\Entity\Game;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\Query\Condition;

/**
 * Provides a list controller for Game entity.
 *
 * @ingroup mespronos
 */
class BetController extends ControllerBase {

  /**
   * Define bets scores for a given game
   * @param \Drupal\mespronos\Entity\Game $game
   */
  public static function updateBetsFromGame(Game $game) {

    $st1 = $game->getScoreTeam1();
    $st2 = $game->getScoreTeam2();
    $points = $game->getLeague()->getPoints();
    $injected_database = Database::getConnection();

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

  /**
   * @param \Drupal\Core\Session\AccountProxy $user
   * @param \Drupal\mespronos\Entity\Game $game
   * @return \Drupal\mespronos\Entity\Bet
   */
  public static function loadForUser(\Drupal\Core\Session\AccountProxy $user,Game $game) {

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
