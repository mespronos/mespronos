<?php

/**
 * @file
 * Contains \Drupal\mespronos\Controller\LeagueController.
 */

namespace Drupal\mespronos\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\mespronos\Entity\League;

/**
 * Class LeagueController.
 *
 * @package Drupal\mespronos\Controller
 */
class LeagueController extends ControllerBase {

  public function index(League $league) {

    return [
        '#type' => 'markup',
        '#markup' => $this->t("Implement method: hello with parameter(s): $league")
    ];
  }
  public function indexTitle(League $league) {
    return $league->label();
  }

}
