<?php

namespace Drupal\mespronos\Entity\Traits;


trait ScoreTeamTrait {

  abstract public function get($name);

  abstract public function id();

  /**
   * @return int|null
   */
  public function getScoreTeam1() {
    return is_numeric($this->get('score_team_1')->value) ? (int) $this->get('score_team_1')->value : NULL;
  }

  /**
   * @return int|null
   */
  public function getScoreTeam2() {
    return is_numeric($this->get('score_team_2')->value) ? (int) $this->get('score_team_2')->value : NULL;
  }

}
