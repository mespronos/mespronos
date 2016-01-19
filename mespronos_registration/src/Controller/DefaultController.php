<?php

/**
 * @file
 * Contains \Drupal\mespronos_registration\Controller\DefaultController.
 */

namespace Drupal\mespronos_registration\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class DefaultController.
 *
 * @package Drupal\mespronos_registration\Controller
 */
class DefaultController extends ControllerBase {
  /**
   * Join.
   *
   * @return string
   *   Return Hello string.
   */
  public function join() {
    $login_form = self::getLoginForm();
    $registration_form = self::getRegistrationForm();
    return [
        '#theme' => 'join',
        '#test_var' => 'looool',
        '#login_form' => render($login_form),
        '#registration_form' => render($registration_form),
    ];
  }

  public static function getLoginForm() {
    $login_form = \Drupal::formBuilder()
      ->getForm('\Drupal\user\Form\UserLoginForm');
    $login_form['name']['#description'] = '';
    $login_form['pass']['#description'] = '';
    return $login_form;
  }

  public static function getRegistrationForm() {
    $account = \Drupal::entityManager()->getStorage('user') ->create(array());
    $form =\Drupal::service('entity.form_builder')->getForm($account, 'default');
    $form['status']['#access'] = false;
    $form['roles']['#access'] = false;
    $form['notify']['#access'] = false;
    $form['user_picture']['#access'] = false;
    $form['contact']['#access'] = false;
    $form['timezone']['#access'] = false;
    return $form;

  }
}
