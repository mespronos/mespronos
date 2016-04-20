<?php

/**
 * @file
 * Contains Drupal\mespronos\Controller\ReminderController.
 */

namespace Drupal\mespronos\Controller;

use Drupal\Core\Controller\ControllerBase;

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
    foreach ($hours as $hour) {

    }
    
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
}
