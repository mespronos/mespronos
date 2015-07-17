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
use Drupal\file\Entity;

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
    $file = File::load($fid);
    if (!$file) {
      throw new \Exception('NotAFileException');
    }
    $yaml = new Parser();
    $data = $yaml->parse(file_get_contents($file->getFileUri()));
    dpm($data);
    $sport = self::importSport($data['league']['sport']);
    $league = self::importLeague($data['league'], $sport);
    foreach($data['league']['days'] as $day) {
      $day = self::importDay($day,$league);
    }

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
      $sport = Sport::create(array(
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

  public static function importLeague($league,Sport $sport) {
    $query = \Drupal::entityQuery('league')->condition('name', '%'.$league['name'].'%','LIKE');
    $id = $query->execute();
    if(count($id) == 0) {
      $league = League::create(array(
        'created' => time(),
        'updated' => time(),
        'creator' => 1,
        'sport' => $sport->id(),
        'name' => $league['name'],
        'classement' => $league['classement'],
        'status' => 'future',
        'langcode' => 'fr',
      ));
      $league->save();
      drupal_set_message(t('The league @league_name of @sport_name has been created',array('@league_name'=> $league['name'],'@sport_name'=>$sport->get('name')->value)));
    }
    else {
      $league = entity_load('league', array_pop($id));
    }
    return $league;
  }

  public static function importDay($day,League $league) {
    $query = \Drupal::entityQuery('day')->condition('number', $day['number']);
    $id = $query->execute();
    if(count($id) == 0) {
      $day = Day::create(array(
        'created' => time(),
        'updated' => time(),
        'creator' => 1,
        'league' => $league->id(),
        'number' => $day['number'],
        'name' => isset($day['name']) ? $day['name'] : t('Journée @nb',array('@nb'=>$day['number'])),
        'langcode' => 'fr',
      ));
      $day->save();
      drupal_set_message(t('The day @number of @league_name has been created',array('@league_name'=> $league->get('name')->value,'@number'=>$day->get('number')->value)));
    }
    else {
      $day = entity_load('league', array_pop($id));
    }
    return $day;
  }
}
