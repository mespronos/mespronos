<?php

namespace Drupal\mespronos\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\mespronos\Entity\Game;
use Drupal\mespronos\Entity\Day;
use Drupal\mespronos\Entity\RankingDay;
use Drupal\Core\Cache\Cache;
use Drupal\mespronos\Entity\RankingLeague;
use Drupal\mespronos\Entity\RankingGeneral;

/**
 * Implements an example form.
 */
class GamesMarks extends FormBase {

  /**
   * {@inheritdoc}.
   */
  public function getFormId() {
    return 'games_marks';
  }

  /**
   * {@inheritdoc}.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $games = $form_state->getBuildInfo()['args'][0];

    $form['games'] = array(
      '#type' => 'container',
      '#tree' => true,
    );
    foreach ($games as $game) {
      $form['games'][$game->id()] = array(
        '#type' => 'fieldset',
        '#title' => $game->label(),
        '#attributes' => array(
          'class' => array('game'),
        ),
      );
      $form['games'][$game->id()]['score_team_1'] = array(
        '#type' => 'number',
        '#min' => 0,
        '#step' => 1,
        '#size' => '5',
      );
      $form['games'][$game->id()]['score_team_2'] = array(
        '#type' => 'number',
        '#min' => 0,
        '#step' => 1,
        '#size' => '5',
      );
    }
    if (count($games) > 0) {
      $form['actions']['#type'] = 'actions';
      $form['actions']['submit'] = array(
        '#type' => 'submit',
        '#value' => $this->t('Send'),
        '#button_type' => 'primary',
      );
    } else {
      $form['no-bet'] = [
        '#markup' => '<p>'.t('There is no game without mark').'</p>'
      ];
    }
    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $games = $form_state->getValue('games');
    foreach ($games as $game_id => $game_data) {
      if ($game_data['score_team_1'] != '' && $game_data['score_team_1'] < 0) {
        $form_state->setErrorByName('games]['.$game_id.'][score_team_1', $this->t("Can't be less than 0."));
      }
      if ($game_data['score_team_2'] != '' && $game_data['score_team_2'] < 0) {
        $form_state->setErrorByName('games]['.$game_id.'][score_team_2', $this->t("Can't be less than 0."));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $games = $form_state->getValue('games');
    $i = 0;
    $days_to_update = [];
    foreach ($games as $game_id => $game_data) {
      if ($game_data['score_team_1'] != '' && $game_data['score_team_2'] != '') {
        $i++;
        $game = Game::load($game_id);
        $game->setScore($game_data['score_team_1'], $game_data['score_team_2']);
        $game->save();
        $days_to_update[$game->getDayId()] = $game->getDayId();
      }
    }
    drupal_set_message($this->t('@nb_mark games updated', array('@nb_mark'=>$i)));
    $i = 0;
    $leagues = [];
    foreach ($days_to_update as $day_id) {
      $i++;
      $day = Day::load($day_id);
      RankingDay::createRanking($day);
      $leagues[] = $day->getLeague();
    }
    foreach ($leagues as $league) {
      RankingLeague::createRanking($league);
    }
    RankingGeneral::createRanking();
    drupal_set_message($this->t('Ranking updated for @nb_ranking days and @nb_leagues leagues', [
      '@nb_ranking'=>count($days_to_update),
      '@nb_leagues'=>count($leagues),
    ]));
    drupal_flush_all_caches();
    Cache::invalidateTags(array('nextbets', 'lastbets', 'ranking'));
  }
}