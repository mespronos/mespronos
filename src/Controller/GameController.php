<?php

/**
 * @file
 * Contains Drupal\mespronos\Controller\GameController.
 */

namespace Drupal\mespronos\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\mespronos\Entity\Day;

/**
 * Provides a controller for Game entity.
 *
 * @ingroup mespronos
 */
class GameController extends ControllerBase{

  /**
   * Return array of games that has no marks setted
   * @param bool $only_past
   * @return \Drupal\mespronos\Entity\Game[]
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
   * Return all games available to bet on a given day
   * @param \Drupal\mespronos\Entity\Day $day
   * @return \Drupal\mespronos\Entity\Game[]
   */
  public static function getGamesToBet(Day $day) {
    $game_storage = \Drupal::entityManager()->getStorage('game');
    $query = \Drupal::entityQuery('game');

    $now = new \DateTime(null, new \DateTimeZone("UTC"));

    $query->condition('day',$day->id());
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
