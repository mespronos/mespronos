<?php

/**
 * @file
 * Contains \Drupal\mespronos\Plugin\Block\UserProfilBlock.
 */

namespace Drupal\mespronos\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\mespronos\Controller\UserController;
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
    $user_picture = UserController::getUserPictureAsRenderableArray($user);
    $ranking = RankingGeneral::getRankingForBetter($user);
    return [
      '#theme' =>'user-block',
      '#user' => [
        'name' => $user->getAccountName(),
        'rank' => $ranking ? $ranking->getPosition() : '/',
        'points' => $ranking ? $ranking->getPoints() : '/',
      ],
      '#links' => [
        'logout' => Link::fromTextAndUrl(t('Log out'),Url::fromRoute('user.logout',[])),
        'myaccount' => Link::fromTextAndUrl(t('My account'),Url::fromRoute('entity.user.edit_form',['user'=>$user->id()]))
      ],
      '#user_picture' => $user_picture,
      '#cache' => [
        'contexts' => ['user'],
        'tags' => [ 'user:'.$user->id(),'user_block'],
      ],
    ];
  }
}
