<?php

/**
 * @file
 * Contains \Drupal\mespronos_registration\Controller\DefaultController.
 */

namespace Drupal\mespronos\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\field\Entity\FieldConfig;
use Drupal\mespronos\Entity\Base\MPNContentEntityBase;
use Drupal\user\Entity\User;
use Drupal\file\Entity\File;
use Drupal\mespronos_group\Entity\Group;

/**
 * Class DefaultController.
 *
 * @package Drupal\mespronos_registration\Controller
 */
class UserController extends ControllerBase {

  public static function getRenderableUser(\Drupal\user\Entity\User $user) {
    $picture = UserController::getUserPictureAsRenderableArray($user, 'mini_thumbnail');
    return [
      '#theme' => 'user-ranking',
      '#user'=> [
        'name'=> $user->getAccountName(),
        'url'=> $user->url(),
        'id'=> $user->id(),
        'avatar'=> $picture,
      ]
    ];
  }

  public static function getUserPictureAsRenderableArray(User $user, $style_name = 'thumbnail') {
    $user_picture = FALSE;
    if ($user->hasField('user_picture')) {
      $user_picture = $user->get('user_picture')->first();
    }
    if ($user_picture && isset($user_picture->getValue()['target_id']) && $user_picture_file = File::load($user_picture->getValue()['target_id'])) {
      return MPNContentEntityBase::getImageAsRenderableArray($user_picture_file, $style_name);
    }
    else {
      $field_info = FieldConfig::loadByName('user', 'user', 'user_picture');
      $image_uuid = $field_info->getSetting('default_image')['uuid'];
      $image = \Drupal::service('entity.repository')->loadEntityByUuid('file', $image_uuid);
      return MPNContentEntityBase::getImageAsRenderableArray($image, $style_name);
    }
  }

  /**
   * @param \Drupal\user\Entity\User|NULL $user
   * @return bool|\Drupal\mespronos_group\Entity\Group[]
   */
  public static function getGroup(User $user = null) {
    if ($user != null && \Drupal::moduleHandler()->moduleExists('mespronos_group')) {
      $groups = Group::getUserGroup($user);
    } else {
      $groups = false;
    }
    return $groups;
  }
}
