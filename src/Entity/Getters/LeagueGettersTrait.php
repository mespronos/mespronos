<?php

namespace Drupal\mespronos\Entity\Getters;

use Drupal\mespronos\Entity\Day;

trait LeagueGettersTrait {

  /**
   * Return all days for league
   * @return \Drupal\mespronos\Entity\Day[]
   */
  public function getDays() {
    $query = \Drupal::entityQuery('day');
    $query->condition('league', $this->id());
    $query->sort('id', 'ASC');
    $ids = $query->execute();

    return Day::loadMultiple($ids);
  }

  public function getLogoUrl() {
    if($logo = $this->get('field_league_logo')->referencedEntities()[0]) {
      return file_create_url($logo->getFileUri());
    }
    return NULL;
  }
}