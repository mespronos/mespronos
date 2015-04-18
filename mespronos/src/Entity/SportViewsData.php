<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\Sport.
 */

namespace Drupal\mespronos\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides the views data for the Sport entity type.
 */
class SportViewsData extends EntityViewsData implements EntityViewsDataInterface {
  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['sport']['table']['base'] = array(
      'field' => 'id',
      'title' => t('Sport'),
      'help' => t('The sport entity ID.'),
    );

    return $data;
  }

}
