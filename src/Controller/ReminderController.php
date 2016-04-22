<?php

/**
 * @file
 * Contains Drupal\mespronos\Controller\ReminderController.
 */

namespace Drupal\mespronos\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\mespronos\Controller\DayController;
use Drupal\mespronos\Entity\Day;

/**
 * Class ReminderController.
 *
 * @package Drupal\mespronos\Controller
 */
class ReminderController extends ControllerBase {

  public static function init() {
    if(!self::isEnabled()) {
      return false;
    }
    $hours = self::getHoursDefined();
    $days = self::getUpcomming($hours);

    
    return true;
  }

  public static function isEnabled() {
    $config =  \Drupal::config('mespronos.reminder');
    return $config->get('enabled') == true ? true : false;
  }

  public static function getHoursDefined() {
    $config =  \Drupal::config('mespronos.reminder');
    $hours = $config->get('hours');
    return !is_null($hours) ? $hours : [];
  }

  /**
 * Return all days that plays between now and $nb_hours;
 * @param int $nb_hours number of hours
 * @return \Drupal\mespronos\Entity\Day[]
 */
  public static function getUpcomming($nb_hours) {
    $date_to = new \DateTime(null,new \DateTimeZone("UTC"));
    $date_to->add(new \DateInterval('PT'.intval($nb_hours).'H'));
    $now = new \DateTime(null, new \DateTimeZone("UTC"));

    $days = [];

    $game_storage = \Drupal::entityManager()->getStorage('game');
    $query = \Drupal::entityQuery('game');

    $query->condition('game_date',$now->format('Y-m-d\TH:i:s'),'>');
    $query->condition('game_date',$date_to->format('Y-m-d\TH:i:s'),'<=');

    $group = $query->orConditionGroup()
      ->condition('score_team_1',null,'is')
      ->condition('score_team_2',null,'is');

    $query->sort('game_date','ASC');
    $query->sort('id','ASC');

    $ids = $query->condition($group)->execute();

    $games = $game_storage->loadMultiple($ids);

    foreach ($games as $game) {
      $day_id = $game->getDayId();
      if(!isset($days[$day_id])) {
        $days[$game->getDayId()] = $game->getDay();
      }
    }
    return $days;
  }

  public static function getUserWithEnabledReminder() {
    $query = \Drupal::entityQuery('user')
      ->condition('status', 1)
      ->condition('field_reminder_enable.value', 1);
    $uids = $query->execute();
    return $uids;
  }

  public static function doUserHasMissingBets($user_id,Day $day) {
    $games = $day->getGamesId();
    

  }



}
