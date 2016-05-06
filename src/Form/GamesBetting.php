<?php

namespace Drupal\mespronos\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Cache\Cache;
use Drupal\user\Entity\User;
use Drupal\mespronos\Entity\Game;
use Drupal\mespronos\Controller\BetController;
use Drupal\mespronos\Controller\GameController;

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

    $betting_type = $league->getBettingType(TRUE);

    if($betting_type == 'score') {
      $text = 'Enter the score for each team.';
    }
    else {
      $text = 'Click on the winner you want to choose, or draw.';
    }
    $form['infos'] = array(
      '#type' => '#markup',
      '#markup' => t($text),
    );

    $form['games'] = array(
      '#type' => 'container',
      '#tree' => true,
    );
    $form['user'] = array(
      '#type' => 'value',
      '#value' => $user->id(),
    );
    foreach($games as $game) {
      $form['games'][$game->id()] = $this->getGameFormInput($user,$game,$betting_type);
    }
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Save my bets'),
      '#button_type' => 'primary',
    );
    return $form;
  }

  protected function getGameFormInput(User $user, Game $game,$betting_type) {

    $bet = BetController::loadForUser($user,$game);
    $item = array(
      '#type' => 'fieldset',
      '#title' => $game->labelDate(),
      '#attributes' => array(
        'class' => array('game','game-wrapper'),
      ),
    );
    $item['token_id'] = array(
      '#type' => 'hidden',
      '#value' =>$game->id(),
    );
    $item['bet_id'] = array(
      '#type' => 'hidden',
      '#value' => $bet->id(),
    );
    if($betting_type == 'score') {
      $item['score_team_1'] = array(
        '#type' => 'number',
        '#min' => 0,
        '#step' => 1,
        '#size' => '1',
        '#default_value' => $bet->getScoreTeam1(),
        '#title' => $game->get('team_1')->entity->label(true),
        '#attributes' => [
          'class' => ['team_1']
        ],
        '#prefix' => '<div class="score_team_wrapper score_team_1_wrapper">',
        '#suffix' => '</div>',
      );
      $item['score_team_2'] = array(
        '#type' => 'number',
        '#min' => 0,
        '#step' => 1,
        '#size' => '1',
        '#default_value' => $bet->getScoreTeam2(),
        '#title' => $game->get('team_2')->entity->label(true),
        '#attributes' => [
          'class' => ['team_2']
        ],
        '#prefix' => '<div class="score_team_wrapper score_team_2_wrapper">',
        '#suffix' => '</div>',
      );
    }
    else {
      $item['winner'] = [
        '#type' => 'radios',
        '#options' => [
          '1' => $game->get('team_1')->entity->label(true),
          'N' => t('Draw'),
          '2' => $game->get('team_2')->entity->label(true),
        ],
        '#prefix' => '<div class="game_wrapper_winner">',
        '#suffix' => '</div>',
      ];
      if(!is_null($bet->getScoreTeam1()) && !is_null($bet->getScoreTeam2())) {
        $winner = null;
        if($bet->getScoreTeam1() == $bet->getScoreTeam2()) {
          $winner = 'N';
        }
        elseif($bet->getScoreTeam1() > $bet->getScoreTeam2()) {
          $winner = '1';
        }
        elseif($bet->getScoreTeam1() < $bet->getScoreTeam2()) {
          $winner = '2';
        }
        $item['winner']['#default_value'] = $winner;
      }
    }
    return $item;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $games = $form_state->getValue('games');
    $day = $this->extractDay($form_state);
    $league = $day->getLeague();
    $betting_type = $league->getBettingType(TRUE);
    foreach($games as $game_id => $game_data) {
      if($betting_type == 'score') {
        if ($game_data['score_team_1'] != '' && $game_data['score_team_1'] < 0) {
          $form_state->setErrorByName('games][' . $game_id . '][score_team_1', $this->t("Can't be less than 0."));
        }
        if ($game_data['score_team_2'] != '' && $game_data['score_team_2'] < 0) {
          $form_state->setErrorByName('games][' . $game_id . '][score_team_2', $this->t("Can't be less than 0."));
        }
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
    $day = $this->extractDay($form_state);
    $league = $day->getLeague();
    $betting_type = $league->getBettingType(TRUE);
    $i = 0;
    $j = 0;
    foreach($games as $game_id => &$game_data) {
      $bet_storage = \Drupal::entityManager()->getStorage('bet');
      if($game_id == $game_data['token_id']) {
        if($betting_type == 'winner') {
          if($game_data['winner'] != '' && in_array($game_data['winner'],array('1','N','2'))) {
            switch ($game_data['winner']) {
              case '1':
                $game_data['score_team_1'] = 1;
                $game_data['score_team_2'] = 0;
                break;
              case '2':
                $game_data['score_team_1'] = 0;
                $game_data['score_team_2'] = 1;
                break;
              case 'N':
                $game_data['score_team_1'] = 0;
                $game_data['score_team_2'] = 0;
                break;
            }
          }
        }
        if(isset($game_data['score_team_1']) && isset($game_data['score_team_2']) && is_numeric($game_data['score_team_1']) && is_numeric($game_data['score_team_2'])) {
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
    }
    if($i>0) {
      drupal_set_message($this->t('@nb_mark bets saved/updated',array('@nb_mark'=>$i)));
    }
    if($j>0) {
      drupal_set_message($this->t('@nb_mark bet couldn\'t be saved or updated',array('@nb_mark'=>$j)),'warning');
    }
    Cache::invalidateTags(array('user:'.$user->id()));
  }

  /**
   * @param $form_state
   * @return \Drupal\mespronos\Entity\Day
   */
  protected function extractDay(FormStateInterface $form_state) {
    return $form_state->getBuildInfo()['args'][0];
  }

  /**
   * @param $form_state
   * @return \Drupal\user\Entity\User
   */
  protected function extractUser(FormStateInterface $form_state) {
    return $form_state->getBuildInfo()['args'][1];
  }

}