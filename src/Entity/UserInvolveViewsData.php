<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\UserInvolve.
 */

namespace Drupal\mespronos\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides the views data for the UserInvolve entity type.
 */
class UserInvolveViewsData extends EntityViewsData implements EntityViewsDataInterface {
  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['mespronos__user_involve']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('UserInvolve'),
      'help' => $this->t('The user_involve entity ID.'),
    );

    return $data;
  }

}
