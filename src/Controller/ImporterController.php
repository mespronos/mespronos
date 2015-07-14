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
    if (!$file) {
      throw new \Exception('NotAFileException');
    }
    $yaml = new Parser();
    $data = $yaml->parse(file_get_contents($file->getFileUri()));

    $sport = self::importSport($data['league']['sport']);
    $league = self::importLeague($data['league']['name'], $sport);

    dpm($sport);
    dpm($league);

    return [

      '#markup' => 'lol'
    ];
  }

  public static function importSport($sport_name) {
    $query = \Drupal::entityQuery('sport')->condition('name', '%'.$sport_name.'%', 'LIKE');
    $id = $query->execute();
    if (count($id) == 0) {
      $sport = entity_create('sport', array(
        'created' => time(),
        'updated' => time(),
        'creator' => 1,
        'name' => $sport_name,
        'langcode' => 'fr',
      ));
      $sport->save();
    }
    else {
      $sport = entity_load('sport', array_pop($id));
    }
    return $sport;
  }

  public static function importLeague($league_name,Sport $sport) {
    $query = \Drupal::entityQuery('league')->condition('name', '%'.$league_name.'%','LIKE');
    $id = $query->execute();
    if(count($id) == 0) {
      $league = entity_create('league', array(
        'created' => time(),
        'updated' => time(),
        'creator' => 1,
        'sport' => $sport->id(),
        'name' => $league_name,
        'status' => 'future',
        'classement' => TRUE,
        'langcode' => 'fr',
      ));
      $league->save();
      drupal_set_message(t('The league @league_name of @sport_name has been created',array('@league_name'=> $league_name,'@sport_name'=>$sport->get('name')->value)));
    }
    else {
      $league = entity_load('league', array_pop($id));
    }
    return $league;
  }
}
