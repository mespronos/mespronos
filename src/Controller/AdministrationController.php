<?php

/**
 * @file
 * Contains Drupal\mespronos\Controller\ImporterController.
 */

namespace Drupal\mespronos\Controller;

use Drupal\Core\Controller\ControllerBase;
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
class ImporterController extends ControllerBase {

  public function setMarks() {
    $form = \Drupal::formBuilder()->getForm('Drupal\mespronos\Form\FormImport');
    return $form;
  }

}
