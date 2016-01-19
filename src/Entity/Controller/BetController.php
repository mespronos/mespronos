<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\Controller\GameListController.
 */

namespace Drupal\mespronos\Entity\Controller;

use Drupal\mespronos\Entity\League;
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
      return $bet_storage->create(array());;
    }
  }

  public static function betsDone(User $user,Day $day) {
    $injected_database = Database::getConnection();
    $query = $injected_database->select('mespronos__bet','b');
    $query->addExpression('count(b.id)','nb_bet');
    $query->join('mespronos__game','g','b.game = g.id');
    $query->condition('g.day',$day->id());
    $query->condition('b.better',$user->id());

    $results = $query->execute()->fetchAssoc();
    $nb_bets = intval($results['nb_bet']);
    return $nb_bets;
  }

  /**
   * @param \Drupal\mespronos\Entity\Bet $bet
   * @param \Drupal\User $user
   */
  public static function isBetAllowed(Bet $bet,User $user) {
    $game = $bet->getGame(true);
    $now = new \DateTime();
    $game_date = new \DateTime($game->getGameDate());
    //@TODO ajouter un tampon, genre 15 minutes
    if($now->diff($game_date)>=0) {
      return true;
    }
    //@TODO test si user involve
    return true;
  }
}
