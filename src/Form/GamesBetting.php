<?php

namespace Drupal\mespronos\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\mespronos\Entity\Controller\BetController;
use Drupal\mespronos\Entity\Controller\GameController;

/**
 * Implements an example form.
 */
class GamesBetting extends FormBase {

  /**
   * {@inheritdoc}.
   */
  public function getFormId() {
    return 'games_betting';
  }

  /**
   * {@inheritdoc}.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $user = $this->extractUser($form_state);
    $day = $this->extractDay($form_state);
    $league = $day->getLeague();
    $games = GameController::getGamesToBet($day);


    $form['infos'] = array(
      '#type' => 'container',
      '#tree' => true,
    );

    $form['infos']['league'] = [
      '#markup' => '<h2>'.$league->label().'</h2>',
    ];
    $form['infos']['day'] = [
      '#markup' => '<h3>'.$day->label().'</h3>',
    ];
    $points = $league->getPoints();
    $form['infos']['points'] = [
      '#markup' => '<h4>'.t('Points :').'</h4>'.
        '<ul>'.
          '<li>'.t('Exact score found : @nb points',array('@nb'=>$points['points_score_found'])).'</li>'.
          '<li>'.t('Winner found : @nb points',array('@nb'=>$points['points_winner_found'])).'</li>'.
          '<li>'.t('Nothing found : @nb points',array('@nb'=>$points['points_participation'])).'</li>'.
        '</ul>'
      ,
    ];


    $form['games'] = array(
      '#type' => 'container',
      '#tree' => true,
    );
    $form['user'] = array(
      '#type' => 'value',
      '#value' => $user->id(),
    );
    foreach($games as $game) {
      $bet = BetController::loadForUser($user,$game);
      $form['games'][$game->id()] = array(
        '#type' => 'fieldset',
        '#title' => $game->label_full(),
        '#attributes' => array(
          'class' => array('game'),
        ),
      );
      $form['games'][$game->id()]['token_id'] = array(
        '#type' => 'hidden',
        '#value' =>$game->id(),
      );
      $form['games'][$game->id()]['bet_id'] = array(
        '#type' => 'hidden',
        '#value' => $bet->id(),
      );
      $form['games'][$game->id()]['score_team_1'] = array(
        '#type' => 'textfield',
        '#size' => '5',
        '#default_value' => $bet->getScoreTeam1(),
        '#title' => $game->get('team_1')->entity->label(),
        '#attributes' => array(
          'class' => array('team_1')
        )
      );
      $form['games'][$game->id()]['score_team_2'] = array(
        '#type' => 'textfield',
        '#size' => '5',
        '#default_value' => $bet->getScoreTeam2(),
        '#title' => $game->get('team_2')->entity->label(),
        '#attributes' => array(
          'class' => array('team_2')
        )
      );
    }
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Save my bets'),
      '#button_type' => 'primary',
    );
    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $games = $form_state->getValue('games');
    foreach($games as $game_id => $game_data) {
      if ($game_data['score_team_1'] != '' && $game_data['score_team_1'] < 0) {
        $form_state->setErrorByName('games][' . $game_id . '][score_team_1', $this->t("Can't be less than 0."));
      }
      if ($game_data['score_team_2'] != '' && $game_data['score_team_2'] < 0) {
        $form_state->setErrorByName('games][' . $game_id . '][score_team_2', $this->t("Can't be less than 0."));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $games = $form_state->getValue('games');
    $user_id = $form_state->getValue('user');
    $user_storage = \Drupal::entityManager()->getStorage('user');
    $user = $user_storage->load($user_id);
    $i = 0;
    $j = 0;
    foreach($games as $game_id => $game_data) {
      $bet_storage = \Drupal::entityManager()->getStorage('bet');
      if($game_data['score_team_1'] != '' && $game_data['score_team_2'] != '' && $game_id == $game_data['token_id']) {
        if($game_data['bet_id'] !== null) {
          $bet = $bet_storage->load($game_data['bet_id']);
        }
        else {
          $bet = $bet_storage->create(array());
          $bet->set('game',$game_id);
          $bet->set('better',$user->id());
        }
        $bet->set('score_team_1',$game_data['score_team_1']);
        $bet->set('score_team_2',$game_data['score_team_2']);
        if($bet->isAllowed()) {
          $bet->save();
          $i++;
        }
        else {
          $j++;
        }
      }
    }
    if($i>0) {
      drupal_set_message($this->t('@nb_mark bets saved/updated',array('@nb_mark'=>$i)));
    }
    if($j>0) {
      drupal_set_message($this->t('@nb_mark bet couldn\'t be saved or updated',array('@nb_mark'=>$j)),'warning');
    }
  }

  /**
   * @param $form_state
   * @return Day
   */
  protected function extractDay(FormStateInterface $form_state) {
    return $form_state->getBuildInfo()['args'][0];
  }

  /**
   * @param $form_state
   * @return \Drupal\User
   */
  protected function extractUser(FormStateInterface $form_state) {
    return $form_state->getBuildInfo()['args'][1];
  }

}