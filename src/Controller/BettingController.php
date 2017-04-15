<?php

/**
 * @file
 * Contains Drupal\mespronos\Controller\DefaultController.
 */

namespace Drupal\mespronos\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\mespronos\Entity\Day;
use Drupal\user\Entity\User;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;
/**
 * Class DefaultController.
 *
 * @package Drupal\mespronos\Controller
 */
class BettingController extends ControllerBase {

  public function bet(Day $day) {
    $user = \Drupal::currentUser();
    $user = User::load($user->id());
    if ($day === NULL) {
      drupal_set_message($this->t('This day doesn\'t exist.'), 'error');
      throw new AccessDeniedHttpException();
    }
    $form = \Drupal::formBuilder()->getForm('Drupal\mespronos\Form\GamesBetting', $day, $user);
    return $form;
  }

  public function betTitle(Day $day) {
    $league = $day->getLeague();
    return t('Bet on @day', array('@day'=>$league->label().' - '.$day->label()));
  }
  
}
