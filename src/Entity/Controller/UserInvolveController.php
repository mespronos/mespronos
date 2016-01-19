<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\Controller\GameListController.
 */

namespace Drupal\mespronos\Entity\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\mespronos\Entity\League;
use Drupal\mespronos\Entity\UserInvolve;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class UserInvolveController extends ControllerBase {

  public function registerUser($league) {
    $uid = \Drupal::currentUser()->id();
    if(!self::isUserInvolve($uid,$league)) {
      $league = League::load($league);
      if(!$league) {
        drupal_set_message(t('Oops, seems that this league doesn\'t exist.'),'error');
        return $this->redirect('<front>');
      }
      if($league->getStatus(true) != 'active') {
        drupal_set_message(t('It\'s not possible to subscribe to this league.'),'error');
        return $this->redirect('<front>');
      }
      $userInvolve = UserInvolve::create(array(
        'created' => time(),
        'updated' => time(),
        'user' => $uid,
        'league' => $league,
      ));
      $userInvolve->save();
    }
    return $this->redirect('mespronos.nextbets');
  }

  public static function isUserInvolve($uid,$league_id) {
    $involvements = \Drupal::entityQuery('user_involve')
      ->condition('user',$uid)
      ->condition('league',$league_id)
      ->execute();
    return count($involvements)>0;
  }
}
