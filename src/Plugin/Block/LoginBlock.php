<?php

/**
 * @file
 * Contains \Drupal\mespronos\Plugin\Block\LoginBlock.
 */

namespace Drupal\mespronos\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\mespronos\Controller\UserController;

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
    $login_form = UserController::getLoginForm();
    return $login_form;
  }

}
