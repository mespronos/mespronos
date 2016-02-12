<?php

/**
 * @file
 * Contains \Drupal\mespronos\Plugin\Block\LoginBlock.
 */

namespace Drupal\mespronos\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\Core\Link;

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
    $login_form = \Drupal::formBuilder()
      ->getForm('\Drupal\user\Form\UserLoginForm');
    $login_form['name']['#description'] = '';
    $login_form['pass']['#description'] = '';
    $login_form['no_account'] = [
      '#markup' => Link::fromTextAndUrl(
        t('No account ? Register now !'),Url::fromRoute('mespronos.join',[])
      )->toString(),
      '#weight' => 100,
    ];
    return $login_form;
  }

}
