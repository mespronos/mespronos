<?php

namespace Drupal\mespronos_group\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Group entities.
 */
class GroupViewsData extends EntityViewsData implements EntityViewsDataInterface {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();
    $data['mespronos__group']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('Group'),
      'help' => $this->t('The Group ID.'),
    );

    $data['mespronos__group']['nb_membres'] = [
      'field' => [
        'title' => $this->t('Members'),
        'id' => 'group_members_number',
        'help' => t('Members within the group'),
      ],
    ];

    return $data;
  }

}
