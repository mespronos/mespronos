<?php

namespace Drupal\mespronos\Entity\Traits;


trait ScoreTeamTrait {

  public function getScoreTeam1() : int {
    return $this->get('score_team_1')->value;
  }

  public function getScoreTeam2() : int {
    return $this->get('score_team_2')->value;
  }

}
