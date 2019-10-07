<?php

/**
 * @file
 * Contains Drupal\mespronos\Controller\DashboardController.
 */

namespace Drupal\mespronos\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\system\Controller\SystemController;

/**
 * Class DashboardController.
 *
 * @package Drupal\mespronos\Controller
 */
class DashboardController extends ControllerBase {

  public function index() {
    $games = GameController::getGameWithoutMarks();
    $marks_form = \Drupal::formBuilder()->getForm('Drupal\mespronos\Form\GamesMarks', $games);
    $stats = \Drupal::service('mespronos.statistics_manager')->getStatistics();
    $build = [];
    $build[] = [
      '#theme' =>'dashboard',
      '#marks_form' => $marks_form,
      '#stats' => $stats,
      '#nextGames' => \Drupal::service('mespronos.statistics_manager')->getNextGamesStats(10),
    ];
    $systemController = \Drupal::getContainer()->get('class_resolver')->getInstanceFromDefinition('\Drupal\system\Controller\SystemController');
    $build[] = $systemController->overview('mespronos.dashboard');
    return $build;
  }

}
