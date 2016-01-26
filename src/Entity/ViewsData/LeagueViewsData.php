<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\League.
 */

namespace Drupal\mespronos\Entity\ViewsData;

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

    $data['mespronos__league']['table']['base'] = array(
      'field' => 'id',
      'title' => t('League'),
      'help' => t('The league entity ID.'),
    );

    return $data;
  }

}
