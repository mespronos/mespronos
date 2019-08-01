<?php
namespace Drupal\mespronos\Service;

use Drupal\mespronos\Entity\League;

class LeagueManager {

  public function getDaysNumber(League $league) {
    $query = \Drupal::entityQuery('day');
    $query->condition('league', $league->id());
    return $query->count()->execute();

  }

}
