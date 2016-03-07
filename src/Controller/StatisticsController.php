<?php

/**
 * @file
 * Contains Drupal\mespronos\Controller\StatisticsController.
 */

namespace Drupal\mespronos\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class DashboardController.
 *
 * @package Drupal\mespronos\Controller
 */
class StatisticsController extends ControllerBase {

  public static function getStatistics() {
    $stats = [];
    $stats['members'] = t('@nb members',['@nb'=>self::getMembersNumber()]);
    $stats['leagues'] = t('@nb leagues',['@nb'=>self::getLeaguesNumber()]);
    $stats['games'] = t('@nb games',['@nb'=>self::getGamesNumber()]);
    $stats['bets'] = t('@nb bets',['@nb'=>self::getBetsNumber()]);
    return $stats;
  }

  private static function getMembersNumber() {
    $query = \Drupal::entityQuery('user');
    $ids = $query->execute();
    return count($ids);
  }

  private static function getGamesNumber() {
    $query = \Drupal::entityQuery('game');
    $ids = $query->execute();
    return count($ids);
  }

  private static function getLeaguesNumber() {
    $query = \Drupal::entityQuery('league');
    $ids = $query->execute();
    return count($ids);
  }

  function getBetsNumber() {
    $query = \Drupal::entityQuery('bet');
    $ids = $query->execute();
    return count($ids);
  }
}
