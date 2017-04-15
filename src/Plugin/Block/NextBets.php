<?php

/**
 * @file
 * Contains Drupal\mespronos\Plugin\Block\NextBets.
 */

namespace Drupal\mespronos\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mespronos\Controller\BettingController;
use Drupal\mespronos\Controller\NextBetsController;
use Drupal\mespronos\Entity\Controller\DayController;
use Drupal\mespronos\Entity\League;
use Drupal\mespronos\Entity\Day;
use Drupal\mespronos\Entity\Controller\BetController;

/**
 * Provides a 'NextBets' block.
 *
 * @Block(
 *  id = "next_bets",
 *  admin_label = @Translation("next_bets"),
 * )
 */
class NextBets extends BlockBase {

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
    $next_bet_controller = new NextBetsController();
    $return = [];
    $next_bet = $next_bet_controller->nextBets(null, 5);
    if ($next_bet) {
      $return['next-bet'] = $next_bet;
    } else {
      $return['next-bet'] = [
        '#markup' => '<p>'.t('No bet for now').'</p>'
      ];
    }
    $return['#cache'] = [
      'contexts' => ['user'],
      'tags' => ['user:'.\Drupal::currentUser()->id(), 'nextbets'],
      'max-age' => '600',
    ];
    return $return;
  }

}
