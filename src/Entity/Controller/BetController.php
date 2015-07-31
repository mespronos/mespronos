<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\Controller\GameListController.
 */

namespace Drupal\mespronos\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;

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
}
