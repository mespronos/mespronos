<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\Bet.
 */

namespace Drupal\mespronos\Entity\ViewsData;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides the views data for the Bet entity type.
 */
class BetViewsData extends EntityViewsData implements EntityViewsDataInterface {
  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['mespronos__bet']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('Bet'),
      'help' => $this->t('The bet entity ID.'),
    );

    return $data;
  }

}
