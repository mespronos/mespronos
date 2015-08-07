<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\Controller\GameListController.
 */

namespace Drupal\mespronos\Entity\Controller;

class UserInvolveController {

  public static function isUserInvolve($uid,$league_id) {
    $involvements = \Drupal::entityQuery('user_involve')
      ->condition('user',$uid)
      ->condition('league',$league_id)
      ->execute();
    return count($involvements)>0;
  }
}
