<?php

/**
 * @file
 * Contains Drupal\mespronos\Controller\ImporterController.
 */

namespace Drupal\mespronos\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\mespronos\Controller\GameController;
use Drupal\mespronos\Form\ImportForm;
use Symfony\Component\Yaml\Parser;

/**
 * Class ImporterController.
 *
 * @package Drupal\mespronos\Controller
 */
class AdministrationController extends ControllerBase {

  public function settings() {
    $form = \Drupal::formBuilder()->getForm('Drupal\mespronos\Form\AdministrationConfigForm');
    return $form;
  }

  public function setMarks() {
    $games = GameController::getGameWithoutMarks();
    if(count($games) == 0) {
      drupal_set_message($this->t('There\'s no game for which mark is not set'));
    }
    $form = \Drupal::formBuilder()->getForm('Drupal\mespronos\Form\GamesMarks',$games);
    return $form;
  }

}
