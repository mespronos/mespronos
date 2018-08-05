<?php

namespace Drupal\mespronos_group\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Provides a views field to flag or unflag the selected content.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("group_members_number")
 */
class MespronosGroupsMembersNumber extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Intentionally do nothing here since we're only providing a link and not
    // querying against a real table column.
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    /** @var \Drupal\mespronos_group\Entity\Group $entity */
    $groupe = $values->_entity;
    return $groupe->getMemberNumber();
  }

}
