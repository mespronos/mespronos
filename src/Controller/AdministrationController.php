<?php

/**
 * @file
 * Contains Drupal\mespronos\Controller\ImporterController.
 */

namespace Drupal\mespronos\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\mespronos\Entity\Controller\GameController;
use Drupal\mespronos\Form\ImportForm;
use Symfony\Component\Yaml\Parser;
use Drupal\mespronos\Entity\League;
use Drupal\mespronos\Entity\Sport;
use Drupal\mespronos\Entity\Day;
use Drupal\mespronos\Entity\Team;
use Drupal\mespronos\Entity\Game;
use Drupal\file\Entity\File;

/**
 * Class ImporterController.
 *
 * @package Drupal\mespronos\Controller
 */
class AdministrationController extends ControllerBase {

  public function setMarks() {
    $games = GameController::getGameWithoutMarks();
    if(count($games) == 0) {
      drupal_set_message($this->t('There\'s no game for which mark is not set'));
    }
    $form = \Drupal::formBuilder()->getForm('Drupal\mespronos\Form\GamesMarks',$games);
    return $form;
  }

}
