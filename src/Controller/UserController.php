<?php

/**
 * @file
 * Contains \Drupal\mespronos_registration\Controller\DefaultController.
 */

namespace Drupal\mespronos\Controller;

use Drupal\Core\Controller\ControllerBase;

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
}
