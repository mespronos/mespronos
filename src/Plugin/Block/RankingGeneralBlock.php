<?php

/**
 * @file
 * Contains \Drupal\mespronos\Plugin\Block\RankingGeneralBlock.
 */

namespace Drupal\mespronos\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\mespronos\Controller\RankingController;

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
    $table_data = RankingController::getRankingGeneral();
    if ($table_data) {
      $build = [
        'table' => $table_data,
        '#cache' => [
          'contexts' => ['user'],
          'tags' => ['user:'.\Drupal::currentUser()->id(), 'ranking'],
        ],
        '#title' => t('General ranking')
      ];
    } else {
      $build = [
        '#markup' => t('No ranking for now'),
        '#cache' => [
          'contexts' => ['user'],
          'tags' => ['user:'.\Drupal::currentUser()->id(), 'ranking'],
        ],
        '#title' => t('General ranking')
      ];
    }

    return $build;
  }

}
