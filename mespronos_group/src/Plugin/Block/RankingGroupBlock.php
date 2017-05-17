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
    $groups = Group::getUserGroup();
    if (!$groups || count($groups) == 0) {
      return [];
    }
    $build = [];
    $render_controller = \Drupal::entityTypeManager()->getViewBuilder('group');
    foreach ($groups as $group) {
      if ($ranking = RankingController::getRankingGeneral($group)) {
        $group_build = [
          'title' => [
            '#markup' => '<h3>'.$group->label().'</h3>'
          ],
          'group_logo' => $render_controller->view($group, 'logo'),
          'table' => $ranking,
          'more-info' => [
            '#markup' => Link::fromTextAndUrl(t('See group'), Url::fromRoute('entity.group.canonical', ['group'=>$group->id()]))->toString(),
          ],
          '#theme_wrappers' => array('container'),
          '#attributes' => array('class' => array('group-ranking')),
        ];
        $build[$group->id()] = $group_build;
      }
    }

    if (count($build)) {
      $build['#cache'] = [
        'contexts' => ['user'],
        'tags' => ['user:'.\Drupal::currentUser()->id(), 'ranking'],
      ];
      $build['#title'] = t('Groups ranking');
    }
    return $build;
  }

}
