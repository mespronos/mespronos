<?php

/**
 * @file
 * Contains \Drupal\mespronos_registration\Controller\DefaultController.
 */

namespace Drupal\mespronos\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Class DefaultController.
 *
 * @package Drupal\mespronos_registration\Controller
 */
class UserController extends ControllerBase {
  /**
   * Join.
   *
   * @return string
   *   Return Hello string.
   */
  public function join() {
    $registration_form = self::getRegistrationForm();
    return [
        '#theme' => 'join',
        '#registration_form' => render($registration_form),
    ];
  }

  public function passwordReset() {
    $registration_form = self::getResetPasswordForm();
    return $registration_form;
  }

  public function login() {
    $login_form = self::getLoginForm();
    return $login_form;
  }

  public static function getRegistrationForm() {
    $account = \Drupal::entityManager()->getStorage('user') ->create(array());
    $form =\Drupal::service('entity.form_builder')->getForm($account, 'default');
    $form['account']['pass']['#description'] = null;
    $form['account']['mail']['#description'] = null;
    $form['account']['name']['#description'] = null;
    $form['status']['#access'] = false;
    $form['roles']['#access'] = false;
    $form['notify']['#access'] = false;
    $form['user_picture']['#access'] = false;
    $form['contact']['#access'] = false;
    $form['timezone']['#access'] = false;
    $form['actions']['submit']['#value'] = t('Create my account');
    return $form;
  }

  public static function getResetPasswordForm() {
    $password_reset_form = \Drupal::formBuilder()
      ->getForm('\Drupal\user\Form\UserPasswordForm');
    unset($password_reset_form['mail']);
    $password_reset_form['actions']['submit']['#value'] = t('Send me password reset instructions');
    return $password_reset_form;
  }

  public static function getLoginForm() {
    $login_form = \Drupal::formBuilder()
      ->getForm('\Drupal\user\Form\UserLoginForm');
    $login_form['name']['#description'] = '';
    $login_form['pass']['#description'] = '';
    $login_form['no_account'] = [
      '#markup' => '<p>'.Link::fromTextAndUrl(
          t('No account ? Register now !'),Url::fromRoute('mespronos.join',[])
        )->toString().'</p>',
      '#weight' => 100,
    ];
    $login_form['password_reset'] = [
      '#markup' => '<p>'.Link::fromTextAndUrl(
          t('Forget your password ?'),Url::fromRoute('mespronos.password-reset',[])
        )->toString().'</p>',
      '#weight' => 101,
    ];
    return $login_form;
  }
}
