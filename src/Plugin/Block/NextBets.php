<?php

/**
 * @file
 * Contains Drupal\mespronos\Plugin\Block\NextBets.
 */

namespace Drupal\mespronos\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mespronos\Entity\Controller\DayController;
use Drupal\mespronos\Entity\Controller\GameController;
use Drupal\mespronos\Entity\Controller\UserInvolveController;
use Drupal\mespronos\Entity\UserInvolve;

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
    global $user;
    $user_involvements = array();
    //$days = DayController::getNextDays($this->configuration['number_of_days_to_display']);
    $games = GameController::getNextGames();
    foreach ($days  as $day) {
      if(!isset($user_involvements[$day->get('league')])) {
        $user_involvements[$day->get('league')] = UserInvolveController::isUserInvolve($user->get('uid'),$day->get('league'));
      }
    }

    dpm($days);
    dpm($user_involvements);

    $build = [];
    $build['next_bets_number_of_days_to_display']['#markup'] = '<p>' . $this->configuration['number_of_days_to_display'] . '</p>';

    return $build;
  }

}
