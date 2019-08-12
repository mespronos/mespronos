<?php

namespace Drupal\mespronos\Event;

use Drupal\mespronos\Entity\Day;
use Drupal\mespronos\Entity\Game;
use Drupal\user\UserInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event that is fired when a user logs in.
 */
class GamesMarksSubmitEvent extends Event {

  const EVENT_NAME = 'mespronos_games_marks_submit';

  /**
   * The Game.
   *
   * @var \Drupal\mespronos\Entity\Game[}
   */
  public $games;

  public function __construct($games) {
    $this->games = $games;
  }

}
