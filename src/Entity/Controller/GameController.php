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

  public static function getNextGames() {
    $game_storage = \Drupal::entityManager()->getStorage('game');

    $now = new \DateTime();

    $query = db_select('mespronos__game','g');
    $query->fields('g',array('id','day'));
    $query->addExpression('min(game_date)','day_date');
    $query->addExpression('count(id)','nb_game');
    $query->condition('game_date',$now->format('Y-m-d\TH:i:s'),'>')
      ->groupBy('day')
      //->orderBy('game_date','ASC')
      ->execute();
    $results = $query->fetchAllAssoc('id');

    dpm($results);

    $games = $game_storage->loadMultiple($results);

    return $games;
  }
}
