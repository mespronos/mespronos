<?php

/**
 * @file
 * Contains Drupal\mespronos\Plugin\Block\NextBets.
 */

namespace Drupal\mespronos\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mespronos\Controller\LastBetsController;
use Drupal\Core\Url;
use Drupal\Core\Link;


/**
 * Provides a 'LastBets' block.
 *
 * @Block(
 *  id = "last_bets",
 *  admin_label = @Translation("last_bets"),
 * )
 */
class LastBets extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['number_of_days_to_display'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Number of days to display'),
      '#description' => $this->t(''),
      '#default_value' => isset($this->configuration['number_of_days_to_display']) ? $this->configuration['number_of_days_to_display'] : 5,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['number_of_days_to_display'] = $form_state->getValue('number_of_days_to_display');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $lastBetsController = new LastBetsController();
    $return = [];
    $return['last-bet'] = $lastBetsController->lastBets(null,10,'BLOCK');
    /*$return['more-last-bets'] = [
      '#markup'=> Link::fromTextAndUrl(t('See more'),Url::fromRoute('mespronos.lastbets'))->toString(),
    ];*/

    return $return;
  }

}
