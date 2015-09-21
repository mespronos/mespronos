<?php

/**
 * @file
 * Contains Drupal\mespronos\Controller\DefaultController.
 */

namespace Drupal\mespronos\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\mespronos\Entity\Controller\GameController;
use Drupal\mespronos\Entity\Controller\UserInvolveController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class DefaultController.
 *
 * @package Drupal\mespronos\Controller
 */
class BettingController extends ControllerBase {
  /**
   * Hello.
   *
   * @return string
   *   Return Hello string.
   */
  public function index() {
    return [
        '#type' => 'markup',
        '#markup' => $this->t('Hello World!', [])
    ];
  }

  public function bet($day) {
    $user = \Drupal::currentUser();
    $user_uid =  $user->id();
    $day_storage = \Drupal::entityManager()->getStorage('day');
    $day = $day_storage->load($day);
    if($day === NULL) {
      drupal_set_message($this->t('This day doesn\'t exist.'),'error');
      throw new AccessDeniedHttpException();
    }
    $league_id =$day->get('league')->first()->getValue()['target_id'];
    if(!UserInvolveController::isUserInvolve($user_uid,$league_id)) {
      drupal_set_message($this->t('You\'re not subscribed to this day'),'warning');
      throw new AccessDeniedHttpException();
    }
    $games_to_bet = GameController::getGamesToBet($day);

    $form = \Drupal::formBuilder()->getForm('Drupal\mespronos\Form\GamesBetting',$games_to_bet,$user);
    return $form;

  }

}
