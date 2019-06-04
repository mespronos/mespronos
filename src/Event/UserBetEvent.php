<?php

namespace Drupal\mespronos\Event;

use Drupal\mespronos\Entity\Day;
use Drupal\user\UserInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event that is fired when a user logs in.
 */
class UserBetEvent extends Event {

  const EVENT_NAME = 'mespronos_user_bet';

  /**
   * The user account.
   *
   * @var \Drupal\user\UserInterface
   */
  public $account;

  /**
   * The Day
   *
   * @var Day
   */
  public $day;

  /**
   * Constructs the object.
   *
   * @param \Drupal\user\UserInterface $account
   *   The account of the user logged in.
   */
  public function __construct(UserInterface $account, Day $day) {
    $this->account = $account;
    $this->day = $day;
  }

}