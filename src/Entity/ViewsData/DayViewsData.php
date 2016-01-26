<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\Day.
 */

namespace Drupal\mespronos\Entity\ViewsData;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides the views data for the Day entity type.
 */
class DayViewsData extends EntityViewsData implements EntityViewsDataInterface
{

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['mespronos__day']['table']['base'] = array(
      'field' => 'id',
      'title' => t('Day'),
      'help' => t('The day entity ID.'),
    );

    return $data;
  }


}
