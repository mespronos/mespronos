<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\Controller\GameListController.
 */

namespace Drupal\mespronos\Entity\Controller;


class DayController {
  public static function getNextDays($nb = 5) {
    $game_storage = \Drupal::entityManager()->getStorage('day');
    $now = new \DateTime();
    $days_ids = \Drupal::entityQuery('day')
      ->condition('day_date',$now->format('Y-m-d\TH:i:s'),'>')
      ->range(0,$nb)
      ->joi
      ->execute();

    $days = $game_storage->loadMultiple($days_ids);

    return $days;
  }
}
