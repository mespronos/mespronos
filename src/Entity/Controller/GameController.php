<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\Controller\GameListController.
 */

namespace Drupal\mespronos\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;
use Drupal\mespronos\Entity\Day;

/**
 * Provides a list controller for Game entity.
 *
 * @ingroup mespronos
 */
class GameController {
  /**
   * {@inheritdoc}
   */
  public static function getGameWithoutMarks($only_past = true) {
    $game_storage = \Drupal::entityManager()->getStorage('game');
    $query = \Drupal::entityQuery('game');

    if($only_past) {
      $now = new \DateTime(null, new \DateTimeZone("UTC"));
      $query->condition('game_date',$now->format('Y-m-d\TH:i:s'),'<');
    }

    $group = $query->orConditionGroup()
      ->condition('score_team_1',null,'is')
      ->condition('score_team_2',null,'is');
    $query->sort('game_date','ASC');
    $ids = $query->condition($group)->execute();

    $games = $game_storage->loadMultiple($ids);

    return $games;
  }

  /**
   * {@inheritdoc}
   */
  public static function getGamesToBet(Day $day) {
    $game_storage = \Drupal::entityManager()->getStorage('game');
    $query = \Drupal::entityQuery('game');

    $now = new \DateTime(null, new \DateTimeZone("UTC"));

    $query->condition('day',$day->id());
    $query->condition('game_date',$now->format('Y-m-d\TH:i:s'),'>');
    $query->condition('game_date',$now->format('Y-m-d\TH:i:s'),'>');

    $group = $query->orConditionGroup()
      ->condition('score_team_1',null,'is')
      ->condition('score_team_2',null,'is');

    $query->sort('game_date','ASC');
    $query->sort('id','ASC');

    $ids = $query->condition($group)->execute();

    $games = $game_storage->loadMultiple($ids);

    return $games;
  }

}
