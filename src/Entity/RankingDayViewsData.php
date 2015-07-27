<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\RankingDay.
 */

namespace Drupal\mespronos\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides the views data for the RankingDay entity type.
 */
class RankingDayViewsData extends EntityViewsData implements EntityViewsDataInterface {
  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['ranking_day']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('RankingDay'),
      'help' => $this->t('The ranking_day entity ID.'),
    );

    return $data;
  }

}
