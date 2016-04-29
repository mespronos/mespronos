<?php

/**
 * @file
 * Contains \Drupal\mespronos\Plugin\Block\RankingGeneralBlock.
 */

namespace Drupal\mespronos_group\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\mespronos\Controller\RankingController;
use Drupal\mespronos_group\Entity\Group;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Provides a 'RankingGeneralBlock' block.
 *
 * @Block(
 *  id = "ranking_group_block",
 *  admin_label = @Translation("Group Ranking Block"),
 * )
 */
class RankingGroupBlock extends BlockBase {


  /**
   * {@inheritdoc}
   */
  public function build() {
    $group = Group::getUserGroup();
    if(!$group){
      return [];
    }
    $build = [
      'table' => RankingController::getRankingGeneral($group),
      'more-info' => [
        '#markup' => Link::fromTextAndUrl(t('See group'),Url::fromRoute('entity.group.canonical',['group'=>$group->id()]))->toString(),
      ],
      '#cache' => [
        'contexts' => ['user'],
        'tags' => [ 'user:'.\Drupal::currentUser()->id(),'ranking'],
      ],
      '#title' => t('Group @group_label - General ranking',['@group_label'=>$group->label()])
    ];
    return $build;
  }

}
