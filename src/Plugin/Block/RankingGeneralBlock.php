<?php

/**
 * @file
 * Contains \Drupal\mespronos\Plugin\Block\RankingGeneralBlock.
 */

namespace Drupal\mespronos\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\mespronos\Controller\RankingController;
use Drupal\mespronos\Entity\RankingGeneral;

/**
 * Provides a 'RankingGeneralBlock' block.
 *
 * @Block(
 *  id = "ranking_general_block",
 *  admin_label = @Translation("General Ranking Block"),
 * )
 */
class RankingGeneralBlock extends BlockBase {


  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build = [
      'table' => RankingController::getRankingGeneral(),
      '#cache' => [
        'contexts' => ['user'],
        'tags' => [ 'user:'.\Drupal::currentUser()->id(),'ranking'],
      ],
      '#title' => t('General ranking')
    ];

    return $build;
  }

}
