<?php

/**
 * @file
 * Contains \Drupal\mespronos\Plugin\Block\UserBlock.
 */

namespace Drupal\mespronos\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\mespronos\Controller\UserController;
use Drupal\mespronos\Entity\RankingGeneral;
use Drupal\mespronos_group\Controller\GroupController;
use Drupal\mespronos_group\Entity\Group;
use Drupal\user\Entity\User;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Provides a 'UserBlock' block.
 *
 * @Block(
 *  id = "user_block",
 *  admin_label = @Translation("User block"),
 * )
 */
class UserBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $user = User::load(\Drupal::currentUser()->id());
    if (!$user) {
      return [
        '#cache' => [
          'contexts' => ['user'],
        ],
      ];
    }
    $user_picture = UserController::getUserPictureAsRenderableArray($user);
    $ranking = RankingGeneral::getRankingForBetter($user);
    $groups = [];
    if(\Drupal::moduleHandler()->moduleExists('mespronos_group') && !\Drupal::service('mespronos.domain_manager')->getGroupFromDomain()) {
      $user_groups = Group::getUserGroup($user);
      if ($user_groups && count($user_groups) > 0) {
        foreach ($user_groups as $user_group) {
          $groups[] = [
            'url' => $user_group->url(),
            'name' => $user_group->getTheName(),
          ];
        }
      }
    }
    return [
      '#theme' => 'user-block',
      '#user' => [
        'name' => $user->getAccountName(),
        'rank' => $ranking ? $ranking->getPosition() : '-',
        'nb_betters' => RankingGeneral::getNumberOfBetters(),
        'points' => $ranking ? $ranking->getPoints() : '-',
        'groups' => $groups,
      ],
      '#links' => [
        'logout' => Link::fromTextAndUrl(t('Log out'),
          Url::fromRoute('user.logout')),
        'myaccount' => Link::fromTextAndUrl(t('See my profile'),
          Url::fromRoute('entity.user.canonical', ['user' => $user->id()])),
        'editmyaccount' => Link::fromTextAndUrl(t('Edit my account'),
          Url::fromRoute('entity.user.edit_form', ['user' => $user->id()])),
      ],
      '#user_picture' => $user_picture,
      '#cache' => [
        'contexts' => ['user'],
        'tags' => ['user:' . $user->id(), 'user_block', 'ranking'],
      ],
    ];
  }

}
