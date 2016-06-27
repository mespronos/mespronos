<?php

/**
 * @file
 * Contains \Drupal\mespronos\Plugin\Block\UserProfilBlock.
 */

namespace Drupal\mespronos\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\mespronos\Controller\BetController;
use Drupal\mespronos\Controller\RankingController;
use Drupal\mespronos\Controller\StatisticsController;
use Drupal\mespronos\Controller\UserController;
use Drupal\mespronos\Entity\Ranking;
use Drupal\mespronos\Entity\RankingGeneral;
use Drupal\user\Entity\User;
use Drupal\Core\Url;
use Drupal\Core\Link;

use Symfony\Component\HttpFoundation\Request;
/**
 * Provides a 'UserProfilBlock' block.
 *
 * @Block(
 *  id = "user_profil_block",
 *  admin_label = @Translation("User profil block"),
 * )
 */
class UserProfilBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $user = \Drupal::routeMatch()->getParameter('user');
    if(!$user) {
      return [];
    }
    $user = User::load($user->id());
    $palmares = RankingController::getPalmares($user);
    $statistics = StatisticsController::getUserStatistics($user);
    $user_picture = UserController::getUserPictureAsRenderableArray($user);
    $ranking = RankingGeneral::getRankingForBetter($user);
    return [
      '#theme' =>'user-profile-block',
      '#user' => [
        'name' => $user->getAccountName(),
        'rank' => $ranking ? $ranking->getPosition() : '/',
        'points' => $ranking ? $ranking->getPoints() : '/',
      ],
      '#statistics' => $statistics,
      '#palmares' => $palmares,
      //'#last_bets' => BetController::getLastUserBetsTable($user,50),
      '#user_picture' => $user_picture,
      '#cache' => [
        'contexts' => ['route'],
        'tags' => [ 'user:'.$user->id(),'user_profil_block'],
      ],
    ];
  }
}
