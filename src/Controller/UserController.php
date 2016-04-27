<?php

/**
 * @file
 * Contains \Drupal\mespronos_registration\Controller\DefaultController.
 */

namespace Drupal\mespronos\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\mespronos\Entity\MPNContentEntityBase;
use Drupal\user\Entity\User;
use Drupal\file\Entity\File;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class DefaultController.
 *
 * @package Drupal\mespronos_registration\Controller
 */
class UserController extends ControllerBase {

  public static function getRenderableUser(\Drupal\user\Entity\User $user) {
    $picture = UserController::getUserPictureAsRenderableArray($user,'mini_thumbnail');
    return [
      '#theme' => 'user-ranking',
      '#user'=> [
        'name'=> $user->getAccountName(),
        'id'=> $user->id(),
        'avatar'=> $picture,
      ]
    ];
  }

  public static function getUserPictureAsRenderableArray(User $user,$style_name = 'thumbnail') {
    $user_picture = $user->get("user_picture")->first();
    if($user_picture && !is_null($user_picture) && $user_picture_file = File::load($user_picture->getValue()['target_id'])) {
      return MPNContentEntityBase::getImageAsRenderableArray($user_picture_file,$style_name);
    }
    else {
      return [];
    }
  }
}
