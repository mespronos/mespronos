<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\RankingGeneral.
 */

namespace Drupal\mespronos\Entity\ViewsData;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides the views data for the RankingLeague entity type.
 */
class RankingGeneralViewsData extends EntityViewsData implements EntityViewsDataInterface {
  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['mespronos__ranking_general']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('RankingGeneral'),
      'help' => $this->t('The ranking_general entity ID.'),
    );

    return $data;
  }

}
