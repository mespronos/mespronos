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
    $bet_storage = \Drupal::entityManager()->getStorage('bet');
    $ids = \Drupal::entityQuery('bet')
      ->condition('game',$game->id())
      ->execute();

    $bets = $bet_storage->loadMultiple($ids);

    foreach($bets as $bet) {
      $bet->definePoints($game);
    }
  }

  public static function nextbets(League $league=null) {
    kint($league);
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
