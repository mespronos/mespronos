<?php

namespace Drupal\mespronos_group\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\mespronos_group\Entity\Group;
use Drupal\mespronos\Controller\RankingController;

/**
 * Provides a 'GroupMembersBlock' block.
 *
 * @Block(
 *  id = "group_ranking_block",
 *  admin_label = @Translation("Group Ranking Page block"),
 * )
 */
class GroupRankingBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    if (\Drupal::routeMatch()->getRouteName() == 'entity.group.canonical') {
      $group = \Drupal::routeMatch()->getParameter('group');
      $build = RankingController::getRankingGeneral($group);

      $build['#cache'] = [
        'contexts' => ['user'],
        'tags' => [ 'user:'.\Drupal::currentUser()->id(),'ranking'],
      ];

      $build['#title'] = t('Group ranking');
      return $build;
    }
    return [];

  }

}
