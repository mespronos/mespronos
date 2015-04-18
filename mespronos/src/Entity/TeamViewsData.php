<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\Team.
 */

namespace Drupal\mespronos\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides the views data for the Team entity type.
 */
class TeamViewsData extends EntityViewsData implements EntityViewsDataInterface {
  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['team']['table']['base'] = array(
      'field' => 'id',
      'title' => t('Team'),
      'help' => t('The team entity ID.'),
    );

    return $data;
  }

}
