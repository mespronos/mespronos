<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\Controller\GameListController.
 */

namespace Drupal\mespronos\Entity\Controller;

use Drupal\mespronos\Entity\League;
use Drupal\mespronos\Entity\Day;
use Drupal\mespronos\Entity\Bet;
use Drupal\mespronos\Entity\Game;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\Query\Condition;

/**
 * Provides a list controller for Game entity.
 *
 * @ingroup mespronos
 */
class BetController {
  /**
   * {@inheritdoc}
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

  public static function nextbets(League $league=null) {
    return ['#markup'=>'loool'];
  }

  /**
   * @param \Drupal\User $user
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

  public static function betsDone(\Drupal\Core\Session\AccountProxy $user,Day $day) {
    return self::getInfos('bets',$user,$day);
  }
  public static function betsLeft(\Drupal\Core\Session\AccountProxy $user,Day $day) {
    $now_date = new \DateTime();
    $now_date->setTimezone(new \DateTimeZone("GMT"));

    $injected_database = Database::getConnection();

    $subquery = $injected_database->select('mespronos__bet','b');
    $subquery->leftJoin('mespronos__game','g','b.game = g.id');
    $subquery->fields('g',['id']);
    $subquery->condition('g.day',$day->id());
    $subquery->condition('b.better',$user->id());
    //$results = $subquery->execute()->fetchAllKeyed(0,0);


    $query = $injected_database->select('mespronos__game','g');
    $query->addExpression('count(g.id)','nb_bet_left');
    $query->condition('g.day',$day->id());
    $query->condition('g.game_date',$now_date->format('Y-m-d\TH:i:s'),'>');
    $query->condition('g.id', $subquery, 'NOT IN');

    $results = $query->execute()->fetchAssoc();
    return $results['nb_bet_left'];
  }

  public static function pointsWon(\Drupal\Core\Session\AccountProxy $user,Day $day) {
    return self::getInfos('points',$user,$day);
  }

  protected static function getInfos($type, \Drupal\Core\Session\AccountProxyInterface $user,Day $day) {
    $injected_database = Database::getConnection();
    $query = $injected_database->select('mespronos__bet','b');
    $query->addExpression('sum(b.points)','points');
    $query->addExpression('count(b.id)','nb_bet');
    $query->join('mespronos__game','g','b.game = g.id');
    $query->condition('g.day',$day->id());
    $query->condition('b.better',$user->id());

    $results = $query->execute()->fetchAssoc();
    if($type == 'points') {
      $points = intval($results['points']);
      return $points;
    }
    else {
      $nb_bets = intval($results['nb_bet']);
      return $nb_bets;
    }
  }

}
