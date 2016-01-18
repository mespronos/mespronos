<?php

/**
 * @file
 * Contains \Drupal\mespronos_registration\Controller\DefaultController.
 */

namespace Drupal\mespronos_registration\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class DefaultController.
 *
 * @package Drupal\mespronos_registration\Controller
 */
class DefaultController extends ControllerBase {
  /**
   * Join.
   *
   * @return string
   *   Return Hello string.
   */
  public function join() {
    return [
        '#type' => 'markup',
        '#markup' => $this->t('Implement method: join')
    ];
  }

}
