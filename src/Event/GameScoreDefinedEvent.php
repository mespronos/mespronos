<?php

namespace Drupal\mespronos\Event;

use Drupal\mespronos\Entity\Day;
use Drupal\mespronos\Entity\Game;
use Drupal\user\UserInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event that is fired when a user logs in.
 */
class GameScoreDefinedEvent extends Event {

  const EVENT_NAME = 'mespronos_game_score_defined';

  /**
   * The Game.
   *
   * @var \Drupal\mespronos\Entity\Game
   */
  public $game;

  public function __construct(Game $game) {
    $this->game = $game;
  }

}