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
class GameController {
  /**
   * {@inheritdoc}
   */
  public static function getGameWithoutMarks() {
    $game_storage = \Drupal::entityManager()->getStorage('game');
    $query = \Drupal::entityQuery('game');

    $group = $query->orConditionGroup()
      ->condition('score_team_1',null,'is')
      ->condition('score_team_2',null,'is');

    $ids = $query->condition($group)->execute();

    $games = $game_storage->loadMultiple($ids);

    return $games;
  }

}
