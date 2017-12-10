<?php

namespace Drupal\mespronos\Entity\Getters;

use Drupal\mespronos\Entity\Day;
use Drupal\mespronos\Entity\League;
use Drupal\mespronos\Entity\Team;

trait GameGettersTrait {

  abstract public function get($name);

  abstract public function id();

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
    return Team::load($this->getTeam1Id());
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
    return Team::load($this->getTeam2Id());
  }

  /**
   * @return League
   */
  public function getLeague() : League {
    $day = Day::load($this->get('day')->target_id);
    return League::load($day->get('league')->target_id);
  }

  /**
   * Return game's day entity
   * @return Day
   */
  public function getDay() : Day {
    return Day::load($this->get('day')->target_id);
  }

  /**
   * Return game's day id
   * @return integer
   */
  public function getDayId() : int {
    return $this->get('day')->target_id;
  }

  public function label() {
    $league = $this->getLeague();
    $day = $this->getDay();
    $team1 = $this->getTeam1();
    $team2 = $this->getTeam2();

    return t('@team1 - @team2 (@league - @day)', [
      '@team1'=> $team1->label(),
      '@team2'=> $team2->label(),
      '@league'=>$league->label(),
      '@day' => $day->label()
    ]);
  }

  public function labelTeams() {
    $team1 = $this->getTeam1();
    $team2 = $this->getTeam2();
    return t('@team1 - @team2', array('@team1'=> $team1->label(), '@team2'=> $team2->label()));
  }

  public function labelTeamsAndHour() {
    $team1 = $this->getTeam1();
    $team2 = $this->getTeam2();
    $date = new \DateTime($this->getGameDate(), new \DateTimeZone('UTC'));
    $date->setTimezone(new \DateTimeZone("Europe/Paris"));


    return [
      '#theme' => 'game-with-flag',
      '#team_1' => [
        'label' => $team1->label(),
        'logo' => $team1->getLogo('mini_logo'),
      ],
      '#team_2' => [
        'label' => $team2->label(),
        'logo' => $team2->getLogo('mini_logo'),
      ],
      '#game' => [
        'date' => $date->format('d/m/Y H\hi'),
      ],
    ];

  }

  public function labelScore() {
    return t('@t1 - @t2', array('@t1'=> $this->get('score_team_1')->value, '@t2'=> $this->get('score_team_2')->value));
  }

  public function labelWithScore() {
    $team1 = $this->getTeam1();
    $team2 = $this->getTeam2();
    return t('<span class="team team-1">@team1</span> <span class="score">@s1 - @s2</span> <span class="team team-2">@team2</span>', array('@team1'=> $team1->label(), '@team2'=> $team2->label(), '@s1'=> $this->get('score_team_1')->value, '@s2'=> $this->get('score_team_2')->value));
  }

  public function labelWithScoreAndLogo() {
    $team1 = $this->getTeam1();
    $logo_team_1 = $team1->getLogo('mini_thumbnail');
    $team2 = $this->getTeam2();
    $logo_team_2 = $team2->getLogo('mini_thumbnail');
    return t('<span class="team team-1">@team1</span> <span class="score">@s1 - @s2</span> <span class="team team-2">@team2</span>', array('@team1'=> render($logo_team_1), '@team2'=> render($logo_team_2), '@s1'=> $this->get('score_team_1')->value, '@s2'=> $this->get('score_team_2')->value));
  }

  public function labelForInsight() {
    $team1 = $this->getTeam1();
    $team2 = $this->getTeam2();
    $league = $this->getLeague();
    $date = new \DateTime($this->getGameDate(), new \DateTimeZone('UTC'));
    $date->setTimezone(new \DateTimeZone("Europe/Paris"));
    return t('@team1 @s1 - @s2 @team2 (@league - @date)', array(
        '@team1'=> $team1->label(),
        '@team2'=> $team2->label(),
        '@s1'=> $this->get('score_team_1')->value,
        '@s2'=> $this->get('score_team_2')->value,
        '@league'=> $league->getTheName(),
        '@date'=> $date->format('d/m/Y'),
      )
    );
  }

  public function label_full() {
    $team1 = $this->getTeam1();
    $team2 = $this->getTeam2();
    $date = new \DateTime($this->getGameDate(), new \DateTimeZone('UTC'));
    $date->setTimezone(new \DateTimeZone("Europe/Paris"));
    return t('@team1 - @team2 - @date', array('@team1'=> $team1->label(), '@team2'=> $team2->label(), '@date'=> $date->format('d/m/Y H\hi')));
  }

  public function labelDate() {
    $date = new \DateTime($this->getGameDate(), new \DateTimeZone('UTC'));
    $date->setTimezone(new \DateTimeZone("Europe/Paris"));
    return \Drupal::service('date.formatter')->format($date->format('U'), 'long');
  }

}