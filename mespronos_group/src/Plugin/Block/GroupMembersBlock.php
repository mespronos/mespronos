<?php

namespace Drupal\mespronos_group\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\mespronos_group\Entity\Group;

/**
 * Provides a 'GroupMembersBlock' block.
 *
 * @Block(
 *  id = "group_members_block",
 *  admin_label = @Translation("Group members block"),
 * )
 */
class GroupMembersBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    if (\Drupal::routeMatch()->getRouteName() == 'entity.group.canonical') {
      $group = \Drupal::routeMatch()->getParameter('group');
      $members = $group->getMembers(true);
      $items = [];
      $render_controller = \Drupal::entityTypeManager()->getViewBuilder('user');
      foreach ($members as $member) {
        $items[] = $render_controller->view($member, 'compact');
      }
      $build = [];
      $build['group_members_block'] = [
        '#theme' => 'item_list',
        '#items' => $items,
        '#list_type' => 'ul',
        '#attributes' => [
          'id' => 'group-members-list'
        ],
        '#cache' => [
          'contexts' => ['user'],
          'tags' => ['group:'.$group->id(), 'groups'],
      ],
      ];
      return $build;
    }
    return [];

  }

}
