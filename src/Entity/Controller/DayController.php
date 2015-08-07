<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\Controller\GameListController.
 */

namespace Drupal\mespronos\Entity\Controller;


class DayController {

  public static function getNextDaysToBet($nb = 5) {
    $day_storage = \Drupal::entityManager()->getStorage('day');

    $now = new \DateTime();

    $query = db_select('mespronos__game','g');
    $query->addExpression('min(game_date)','day_date');
    $query->addExpression('count(id)','nb_game');
    $query->groupBy('day');
    $query->fields('g',array('day'));

    $query->condition('game_date',$now->format('Y-m-d\TH:i:s'),'>');
    $query->orderBy('day_date','ASC');
    $query->range(0,$nb);
    $results = $query->execute();
    $results = $results->fetchAllAssoc('day');
    $days = $day_storage->loadMultiple(array_keys($results));

    foreach($results as $key => &$day_data) {
     $day_data->entity = $days[$key];
    }
    return $results;
  }
}
