<?php

/**
 * @file
 * Contains \Drupal\mespronos\Plugin\Block\RankingGeneralBlock.
 */

namespace Drupal\mespronos\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\mespronos\Controller\RankingController;
use Drupal\mespronos\Controller\UserController;
use Drupal\mespronos_tweaks\UserHelper;

/**
 * Provides a 'RankingGeneralBlock' block.
 *
 * @Block(
 *  id = "podium_block",
 *  admin_label = @Translation("PodiumBlock"),
 * )
 */
class PodiumBlock extends BlockBase {


  /**
   * {@inheritdoc}
   */
  public function build() {
    /** @var \Drupal\mespronos\Entity\RankingGeneral[] $rankings */
    $rankings = \Drupal::service('mespronos.ranking_manager')->getTop();
    $podium = [];
    foreach ($rankings as $ranking) {
      $better = $ranking->getOwner();
      $podium[] = [
        'better' => $better,
        'better_avatar' => UserController::getUserPictureAsRenderableArray($better, 'podium'),
        'better_name' => $better->getDisplayName(),
        'position' => $ranking->getPosition(),
        'points' => $ranking->getPoints(),
        'bets' => $ranking->getGameBetted(),
        'average' => number_format($ranking->getPoints() / $ranking->getGameBetted(),3),
        'url' => $better->url(),
      ];
    }
    $build =  [
      '#theme' => 'podium',
      '#data' => $podium,
      '#cache' => [
        'tags' => [
          'ranking'
        ],
      ],
    ];


    if($group = \Drupal::service('mespronos.domain_manager')->getGroupFromDomain()) {
      $build['#cache']['tags'][] = 'group:' . $group->id();
    }
    else {
      $build['#cache']['tags'][] = 'group:main';
    }
    return $build;
  }

}
