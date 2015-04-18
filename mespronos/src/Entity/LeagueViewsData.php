<?php

/**
 * @file
 * Contains Drupal\mespronos_leagues\Entity\League.
 */

namespace Drupal\mespronos_leagues\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides the views data for the League entity type.
 */
class LeagueViewsData extends EntityViewsData implements EntityViewsDataInterface {
  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['league']['table']['base'] = array(
      'field' => 'id',
      'title' => t('League'),
      'help' => t('The league entity ID.'),
    );

    return $data;
  }

}
