<?php

namespace Drupal\mespronos\Entity\ViewsData;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the Game entity type.
 */
class GameViewsData extends EntityViewsData {

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
    $data['mespronos__game']['bets'] = [
      'title' => t('Bets'),
      'help' => t('Link to bets on current game'),
      'relationship' => [
        'group' => t('game'),
        'label' => t('Bets de formation'),
        'base' => 'mespronos__bet',
        'field table' => 'mespronos__bet',
        'base field' => 'game',
        'relationship field' => 'id',
        'id' => 'standard',
      ],
    ];
    return $data;
  }

}
