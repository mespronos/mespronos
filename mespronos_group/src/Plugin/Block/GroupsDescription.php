<?php

namespace Drupal\mespronos_group\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'GroupsDescription' block.
 *
 * @Block(
 *  id = "groups_description",
 *  admin_label = @Translation("Groups description"),
 * )
 */
class GroupsDescription extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    return ['#theme' => 'group-description'];

  }

}
