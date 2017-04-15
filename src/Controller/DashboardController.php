<?php

/**
 * @file
 * Contains Drupal\mespronos\Controller\DashboardController.
 */

namespace Drupal\mespronos\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class DashboardController.
 *
 * @package Drupal\mespronos\Controller
 */
class DashboardController extends ControllerBase {
  /**
   * Hello.
   *
   * @return string
   *   Return Hello string.
   */
  public function index() {
    $games = GameController::getGameWithoutMarks();
    $marks_form = \Drupal::formBuilder()->getForm('Drupal\mespronos\Form\GamesMarks', $games);
    $stats = StatisticsController::getStatistics();
    return [
      '#theme' =>'dashboard',
      '#marks_form' => $marks_form,
      '#stats' => $stats,
    ];
  }

}
