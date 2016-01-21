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
use Drupal\mespronos\Entity\Controller\DayController;
use Drupal\mespronos\Entity\League;
use Drupal\mespronos\Entity\Controller\BetController;


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
    $user = \Drupal::currentUser();
    $user_uid =  $user->id();
    $days = DayController::getlastDays($this->configuration['number_of_days_to_display']);
    $rows = [];
    foreach ($days  as $day_id => $day) {
      $league_id = $day->entity->get('league')->first()->getValue()['target_id'];
      if(!isset($leagues[$league_id])) {
        $leagues[$league_id] = League::load($league_id);
      }
      $league = $leagues[$league_id];

      $bets_done = BetController::betsDone($user,$day->entity);
      $points_won = BetController::PointsWon($user,$day->entity);
      if($user_uid>0) {
        $action_links = Link::fromTextAndUrl(
            t('Details'),
            Url::fromRoute('mespronos.lastbetsdetails',['day'=>$day->entity->id()])
        );
      }
      else {
        $action_links = Link::fromTextAndUrl(
            t('Log in to see your score'),
            Url::fromRoute('user.login',[],[
                    'query' => [
                        'destination' => Url::fromRoute('mespronos.lastbetsdetails',['day'=>$day->entity->id()])->toString(),
                    ]
                ]
            )
        );
      }

      $row = [
        $league->label(),
        $day->entity->label(),
        $day->nb_game_over,
        $day->nb_game_with_score,
        $user_uid > 0 ? $bets_done : '/',
        $user_uid > 0 ? $points_won : '/',
        $action_links,

      ];
      $rows[] = $row;
    }
    $footer = [
      'data' => array(
        array(
          'data' => Link::fromTextAndUrl(
            $this->t('See all the last bets'),
            new Url('mespronos.lastbets')
          ),
          'colspan' => 7
        )
      )
    ];
    $header = [
      $this->t('League',array(),array('context'=>'mespronos')),
      $this->t('Day',array(),array('context'=>'mespronos')),
      $this->t('Games over',array(),array('context'=>'mespronos')),
      $this->t('Games with score',array(),array('context'=>'mespronos')),
      $this->t('Bets done',array(),array('context'=>'mespronos')),
      $this->t('Points',array(),array('context'=>'mespronos')),
      '',

    ];
    return [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
      '#footer' => $footer,
      '#cache' => [
        'contexts' => [
          'user',
        ],
      ],
    ];

  }

}
