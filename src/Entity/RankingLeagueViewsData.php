<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\RankingLeague.
 */

namespace Drupal\mespronos\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides the views data for the RankingLeague entity type.
 */
class RankingLeagueViewsData extends EntityViewsData implements EntityViewsDataInterface {
  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['ranking_league']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('RankingLeague'),
      'help' => $this->t('The ranking_league entity ID.'),
    );

    return $data;
  }

}
