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

  public function remove() {
    $form =  \Drupal::formBuilder()->getForm('\Drupal\mespronos\Form\RemoveDataForm');
    return $form;
  }

  public static function import($fid) {
    $file = File::load($fid);
    if (!$file) {
      throw new \Exception('NotAFileException');
    }
    $yaml = new Parser();
    $data = $yaml->parse(file_get_contents($file->getFileUri()));
    $sport = self::importSport($data['league']['sport']);
    $league = self::importLeague($data['league'], $sport);
    $games = [];
    foreach($data['league']['days'] as $_day) {
      $day = self::importDay($_day,$league);
      foreach($_day['games'] as $_game) {
        $teams = explode('|',$_game['game']);
        $team_1 = self::importTeam(trim(array_shift($teams)));
        $team_2 = self::importTeam(trim(array_shift($teams)));
        $date = isset($_game['game_date']) ? trim($_game['game_date']) : trim($_day['day_default_date']);
        $date = \DateTime::createFromFormat('U',$date,new \DateTimeZone(drupal_get_user_timezone()));
        $date->setTimezone(new \DateTimeZone('UTC'));
        $date = $date->format('Y-m-d\TH:i:s');
        $games[] = self::importGame($_game,$date,$team_1,$team_2,$day,$league);
      }
    }
    return [
      '#markup' => t('@nb games created / updated',array('@nb' => count($games)))
    ];
  }

  public static function importSport($sport_name) {
    $query = \Drupal::entityQuery('sport')->condition('name', '%'.$sport_name.'%', 'LIKE');
    $id = $query->execute();
    if (count($id) == 0) {
      $sport = Sport::create(array(
        'name' => $sport_name,
      ));
      $sport->save();
    }
    else {
      $sport = entity_load('sport', array_pop($id));
    }
    return $sport;
  }

  public static function importLeague($_league,Sport $sport) {
    $query = \Drupal::entityQuery('league')->condition('name', '%'.$_league['name'].'%','LIKE');
    $id = $query->execute();
    if(count($id) == 0) {
      $league = League::create(array(
        'sport' => $sport->id(),
        'name' => $_league['name'],
        'betting_type' => $_league['betting_type'],
        'classement' => $_league['classement'],
        'status' => $_league['status'],
      ));
      $league->save();
      drupal_set_message(t('The league @league_name of @sport_name has been created',array('@league_name'=> $_league['name'],'@sport_name'=>$sport->get('name')->value)));
    }
    else {
      $league = entity_load('league', array_pop($id));
    }
    return $league;
  }

  public static function importDay($day,League $league) {
    $query = \Drupal::entityQuery('day')
      ->condition('number', $day['number'])
      ->condition('league', $league->id());
    $id = $query->execute();
    if(count($id) == 0) {
      $day = Day::create(array(
        'league' => $league->id(),
        'number' => $day['number'],
        'name' => isset($day['name']) ? $day['name'] : t('Journée @nb',array('@nb'=>$day['number'])),
      ));
      $day->save();
      drupal_set_message(t('The day @number of @league_name has been created',array('@league_name'=> $league->get('name')->value,'@number'=>$day->get('number')->value)));
    }
    else {
      $day = entity_load('day', array_pop($id));
    }
    return $day;
  }

  public static function importTeam($team_name) {
    $query = \Drupal::entityQuery('team')->condition('name', $team_name);
    $id = $query->execute();
    if(count($id) == 0) {
      $team = Team::create(array(
        'name' => $team_name,
      ));
      $team->save();
      drupal_set_message(t('The team @team has been created',array('@team'=> $team_name)));
    }
    else {
      $team = entity_load('team', array_pop($id));
    }
    return $team;
  }

  public static function importGame($_game,$date,Team $team_1,Team $team_2,Day $day, League $league) {
    $query = \Drupal::entityQuery('game')
      ->condition('team_1', $team_1->id())
      ->condition('team_2', $team_2->id())
      ->condition('day', $day->id());
    $id = $query->execute();
    if(isset($_game['mark'])) {
      $mark = explode('|',$_game['mark']);
      $score_team_1 = trim(array_shift($mark));
      $score_team_2 = trim(array_shift($mark));
    }
    else {
      $score_team_1 = $score_team_2 = null;
    }
    if(count($id) == 0) {
      $game = Game::create(array(
        'team_1' => $team_1->id(),
        'team_2' => $team_2->id(),
        'score_team_1' => $score_team_1,
        'score_team_2' => $score_team_2,
        'day' => $day->id(),
        'game_date' => $date,
      ));
      $game->save();
      drupal_set_message(t('The game @team1 - @team2 has been created',array('@team1'=> $team_1->get('name')->value,'@team2'=> $team_2->get('name')->value)));
    }
    else {
      $game = entity_load('game', array_pop($id));
      if($game->get('score_team_1') != $score_team_1) {
        $game->set('score_team_1',$score_team_1);
      }
      if($game->get('score_team_2') != $score_team_2) {
        $game->set('score_team_2',$score_team_2);
      }
      if($game->get('game_date')->value != $date) {
        $game->set('game_date',$date);
      }
      $game->save();
    }
    return $game;
  }
}
