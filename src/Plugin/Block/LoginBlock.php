<?php

/**
 * @file
 * Contains \Drupal\mespronos\Plugin\Block\LoginBlock.
 */

namespace Drupal\mespronos\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\mespronos_tweaks\Controller\UserController;

/**
 * Provides a 'LoginBlock' block.
 *
 * @Block(
 *  id = "login_block",
 *  admin_label = @Translation("Login Block"),
 * )
 */
class LoginBlock extends BlockBase {


  /**
   * {@inheritdoc}
   */
  public function build() {
    if(\Drupal::moduleHandler()->moduleExists('mespronos_tweaks')) {
      $login_form = UserController::getLoginForm();
    }
    else {
      $login_form = \Drupal::formBuilder()->getForm('\Drupal\user\Form\UserLoginForm');
    }
    return $login_form;
  }

}
