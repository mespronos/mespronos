<?php

/**
 * @file
 * Contains Drupal\mespronos\Controller\StatisticsController.
 */

namespace Drupal\mespronos\Controller;

use Drupal\Core\Controller\ControllerBase; ;
use Drupal\user\Entity\User;

/**
 * Class StatisticsController.
 *
 * @package Drupal\mespronos\Controller
 */
class StatisticsController extends ControllerBase {

  public static function getStatistics() {
    $stats = [];
    $stats['members'] = t('@nb members', ['@nb'=>self::getMembersNumber()]);
    $stats['leagues'] = t('@nb leagues', ['@nb'=>self::getLeaguesNumber()]);
    $stats['games'] = t('@nb games', ['@nb'=>self::getGamesNumber()]);
    $stats['bets'] = t('@nb bets', ['@nb'=>self::getBetsNumber()]);
    $stats['groups'] = t('@nb groups', ['@nb'=>self::getGroupsNumber()]);
    return $stats;
  }

  public static function getUserStatistics(User $user) {
    $stats = [];
    $stats['nb_bets'] = self::getBetsNumber($user);
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

  private static function getGroupsNumber() {
    $query = \Drupal::entityQuery('group');
    $ids = $query->execute();
    return count($ids);
  }

  private static function getBetsNumber(User $user = null) {
    $query = \Drupal::entityQuery('bet');
    if ($user) {
      $query->condition('better', $user->id());
    }
    $ids = $query->execute();
    return count($ids);
  }

}
