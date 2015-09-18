<?php

/**
 * @file
 * Contains Drupal\mespronos\Plugin\Block\NextBets.
 */

namespace Drupal\mespronos\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mespronos\Entity\Controller\DayController;
use Drupal\mespronos\Entity\Controller\UserInvolveController;
use Drupal\mespronos\Entity\League;

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
    $user_uid =  \Drupal::currentUser()->id();
    $user_involvements = array();
    $days = DayController::getNextDaysToBet($this->configuration['number_of_days_to_display']);
    $rows = [];
    foreach ($days  as $day_id => $day) {
      $league_id = $day->entity->get('league')->first()->getValue()['target_id'];
      if(!isset($leagues[$league_id])) {
        $leagues[$league_id] = League::load($league_id);
      }
      $league = $leagues[$league_id];
      if(!isset($user_involvements[$league_id])) {
        $user_involvements[$league_id] = UserInvolveController::isUserInvolve($user_uid ,$league_id);
      }
      $day->involve = $user_involvements[$league_id];

      $game_date = \DateTime::createFromFormat('Y-m-d\TH:i:s',$day->day_date);
      $now_date = new \DateTime();

      $i = $game_date->diff($now_date);

      if($day->involve) {
        $action_links = \Drupal::l(
          $this->t('Bet now'),
          new Url('mespronos.day.bet', array('day' => $day_id))
        );
      }
      else {
        $action_links = \Drupal::l(
          $this->t('Subscribe now !'),
          new Url('mespronos.league.register', array('league' => $league->id()))
        );
      }

      $row = [
        $league->label(),
        $day->entity->label(),
        $day->nb_game,

        $i->format('%a') >0 ? $this->t('@d days, @GH@im',array('@d'=>$i->format('%a'),'@G'=>$i->format('%H'),'@i'=>$i->format('%i'))) : $this->t('@GH@im',array('@G'=>$i->format('%H'),'@i'=>$i->format('%i'))),
        $action_links,
      ];


      $rows[] = $row;
    }
    $header = [
      $this->t('League'),
      $this->t('Day'),
      $this->t('Game to bet on'),
      $this->t('Time left'),
      '',
    ];

    return [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

  }

}
