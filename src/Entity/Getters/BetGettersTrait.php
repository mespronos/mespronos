<?php

namespace Drupal\mespronos\Entity\Getters;

use Drupal\mespronos\Entity\Game;
use Drupal\mespronos\Entity\Team;

trait BetGettersTrait {

  public function getTeam1() : Team {
    $game = $this->getGame(TRUE);
    return $game->getTeam1();
  }

  public function getTeam2() : Team {
    $game = $this->getGame(TRUE);
    return $game->getTeam2();
  }

  /**
   * @param bool|FALSE $asEntity
   * @return \Drupal\mespronos\Entity\Game
   */
  public function getGame($asEntity = FALSE) {
    $game = $this->get('game')->target_id;
    if ($asEntity) {
      $game = Game::load($game);
    }
    return $game;
  }

  public function getPoints() {
    return $this->get('points')->value;
  }
}