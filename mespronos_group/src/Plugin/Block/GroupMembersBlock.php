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
    $group = FALSE;
    if (\Drupal::routeMatch()->getRouteName() === 'entity.group.canonical') {
      $group = \Drupal::routeMatch()->getParameter('group');
    }
    if($domainGroup = \Drupal::service('mespronos.domain_manager')->getGroupFromDomain()) {
      $group = $domainGroup;
    }
    if ($group) {
      $members = $group->getMembers(TRUE);
      $build['group_members_block'] = [
        '#attributes' => [
          'id' => 'group-members-list',
        ],
        '#cache' => [
          'contexts' => ['user'],
          'tags' => ['group:' . $group->id(), 'groups'],
        ],
      ];
      $render_controller = \Drupal::entityTypeManager()->getViewBuilder('user');
      foreach ($members as $member) {
        $build['group_members_block'][] = $render_controller->view($member, 'compact');
      }
      return $build;
    }
    return ['#cache' => ['max-age' => 0]];

  }

}
