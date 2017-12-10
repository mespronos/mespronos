<?php

namespace Drupal\mespronos\Entity\Getters;

trait LeagueGettersTrait {

  public function getLogoUrl() {
    if($logo = $this->get('field_league_logo')->referencedEntities()[0]) {
      return file_create_url($logo->getFileUri());
    }
    return NULL;
  }
}