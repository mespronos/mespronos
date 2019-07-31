<?php

namespace Drupal\mespronos\Service;

use Drupal\mespronos\BetManager;

class DayManager {

  /**
   * @var \Drupal\mespronos\BetManager
   */
  protected $betManager;

  public function __construct(BetManager $betManager) {
    $this->betManager = $betManager;
  }

}
