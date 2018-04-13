<?php

namespace Drupal\mespronos_group\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\mespronos_group\Controller\GroupController;
use Drupal\user\Entity\User;
use Drupal\mespronos_group\Entity\Group;

/**
 * Provides a 'MyGroups' block.
 *
 * @Block(
 *  id = "my_groups",
 *  admin_label = @Translation("My groups"),
 * )
 */
class MyGroups extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $user = \Drupal::currentUser();
    $user = User::load($user->id());
    $user_groups = Group::getUserGroup($user);
    if ($user_groups && count($user_groups) > 0) {
      $user_groups = GroupController::parseGroupsForListing($user_groups);
    }
    $build = [];
    $build['#title'] = '';

    if ($user_groups && count($user_groups) == 1) {
      $build['#title'] = t('My group');
    }
    elseif (count($user_groups) > 1) {
      $build['#title'] = t('My groups');
    }
    $build['my_groups'] = [
      '#theme' => 'group-list',
      '#display_titles' => TRUE,
      '#user_groups' => $user_groups,
      '#cache' => [
        'contexts' => ['user'],
        'tags' => ['user:' . $user->id(), 'groups'],
        'max-age' => '120',
      ],
    ];

    return $build;
  }

}
