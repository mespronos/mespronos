<?php

namespace Drupal\mespronos\Entity\Getters;

use Drupal\mespronos\Entity\Day;
use Drupal\mespronos\Entity\League;
use Drupal\mespronos\Entity\Team;

trait GameGettersTrait {

  /**
   * Return number of games on current day
   *
   * @return integer nb bets for given game
   */
  public function getNbBets() {
    $query = \Drupal::entityQuery('bet');
    $query->condition('game', $this->id());
    $ids = $query->execute();
    return count($ids);
  }
  /**
   * @return bool
   */
  public function isScoreSetted() {
    return NULL !== $this->getScoreTeam1() && NULL !== $this->getScoreTeam2();
  }

  public function getWinner() {
    if(!$this->isScoreSetted()) {
      return FALSE;
    }
    if($this->getScoreTeam1() > $this->getScoreTeam2()) {
      return 1;
    }
    if($this->getScoreTeam1() < $this->getScoreTeam2()) {
      return 2;
    }
    return 'N';
  }

  public function getGameDate() {
    return $this->get('game_date')->value;
  }

  /**
   * Return Team1 id
   * @return integer
   */
  public function getTeam1Id() : int {
    return $this->get('team_1')->target_id;
  }

  /**
   * Return Team1 entity
   * @return Team
   */
  public function getTeam1() : Team {
    $team_storage = \Drupal::entityTypeManager()->getStorage('team');
    $team = $team_storage->load($this->getTeam1Id());
    return $team;
  }

  /**
   * Return Team2 id
   * @return integer
   */
  public function getTeam2Id() : int {
    return $this->get('team_2')->target_id;
  }

  /**
   * Return Team2 entity
   * @return Team
   */
  public function getTeam2() : Team {
    $team_storage = \Drupal::entityTypeManager()->getStorage('team');
    $team = $team_storage->load($this->getTeam2Id());
    return $team;
  }


  /**
   * @return League
   */
  public function getLeague() : League {
    $day_storage = \Drupal::entityTypeManager()->getStorage('day');
    $league_storage = \Drupal::entityTypeManager()->getStorage('league');
    $day = $day_storage->load($this->get('day')->target_id);
    $league = $league_storage->load($day->get('league')->target_id);
    return $league;
  }



  /**
   * Return game's day entity
   * @return Day
   */
  public function getDay() : Day {
    $day_storage = \Drupal::entityTypeManager()->getStorage('day');
    $day = $day_storage->load($this->get('day')->target_id);
    return $day;
  }

  /**
   * Return game's day id
   * @return integer
   */
  public function getDayId() : int {
    return $this->get('day')->target_id;
  }

}