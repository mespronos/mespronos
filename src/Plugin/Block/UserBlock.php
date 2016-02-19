<?php

/**
 * @file
 * Contains \Drupal\mespronos\Plugin\Block\UserBlock.
 */

namespace Drupal\mespronos\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\mespronos\Entity\RankingGeneral;
use Drupal\user\Entity\User;
use Drupal\file\Entity\File;
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
    $user_picture = $this->getUserPictureRendarableArray();
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

  public function getUserPictureRendarableArray() {

    $user = User::load(\Drupal::currentUser()->id());
    $user_picture = $user->get("user_picture")->first();
    if($user_picture) {
      $user_picture = File::load($user_picture->getValue()['target_id']);
      $variables = array(
        'style_name' => 'thumbnail',
        'uri' => $user_picture->getFileUri(),
      );
      $image = \Drupal::service('image.factory')->get($user_picture->getFileUri());
      if ($image->isValid()) {
        $variables['width'] = $image->getWidth();
        $variables['height'] = $image->getHeight();
      }
      else {
        $variables['width'] = $variables['height'] = NULL;
      }

      $logo_render_array = [
        '#theme' => 'image_style',
        '#width' => $variables['width'],
        '#height' => $variables['height'],
        '#style_name' => $variables['style_name'],
        '#uri' => $variables['uri'],
      ];
    }
    else {
      $logo_render_array = [];
    }
    return $logo_render_array;

  }


}
