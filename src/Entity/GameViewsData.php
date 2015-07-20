<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\Game.
 */

namespace Drupal\mespronos\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides the views data for the Game entity type.
 */
class GameViewsData extends EntityViewsData implements EntityViewsDataInterface {
  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['mespronos__game']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('Game'),
      'help' => $this->t('The game entity ID.'),
    );

    return $data;
  }

}
