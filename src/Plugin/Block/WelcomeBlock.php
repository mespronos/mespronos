<?php

/**
 * @file
 * Contains \Drupal\mespronos\Plugin\Block\WelcomeBlock.
 */

namespace Drupal\mespronos\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'WelcomeBlock' block.
 *
 * @Block(
 *  id = "welcome_block",
 *  admin_label = @Translation("Welcome block"),
 * )
 */
class WelcomeBlock extends BlockBase {


  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['mespronos_welcome_message'] = array(
      '#type' => 'text_format',
      '#title' => $this->t('Welcome Message to display'),
      '#description' => $this->t(''),
      '#default_value' => isset($this->configuration['mespronos_welcome_message']) ? $this->configuration['number_of_days_to_display'] : '',
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['mespronos_welcome_message'] = $form_state->getValue('mespronos_welcome_message')['value'];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['welcome_block']['#markup'] = $this->configuration['mespronos_welcome_message'];

    return $build;
  }

}
