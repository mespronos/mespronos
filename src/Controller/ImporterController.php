<?php

/**
 * @file
 * Contains Drupal\mespronos\Controller\ImporterController.
 */

namespace Drupal\mespronos\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\mespronos\Form\ImportForm;
use Symfony\Component\Yaml\Parser;
/**
 * Class ImporterController.
 *
 * @package Drupal\mespronos\Controller
 */
class ImporterController extends ControllerBase {
  /**
   * Index.
   *
   * @return string
   *   Return Hello string.
   */
  public function index() {
    $form = \Drupal::formBuilder()->getForm('Drupal\mespronos\Form\FormImport');
    return $form;
  }

  public static function import($fid) {
    $file = file_load($fid);
    if(!$file) {
      throw new \Exception('NotAFileException');
    }
    $yaml = new Parser();
    $data = $yaml->parse(file_get_contents($file->getFileUri()));
    return [
      '#markup' => 'lol'
    ];
  }

}
