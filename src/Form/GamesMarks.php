<?php

namespace Drupal\mespronos\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
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
    $form['#attached']['library'][] = 'mespronos/administration_style';

    $form['games'] = array(
      '#type' => 'container'
    );
    foreach($games as $game) {
      $form['games'][$game->id()] = array(
        '#type' => 'fieldset',
        '#title' => $game->label(),
        '#attributes' => array(
          'class' => array('game'),
        ),
      );
      $form['games'][$game->id()]['score_team_1'] = array(
        '#type' => 'textfield',
        '#size' => '5',
      );
      $form['games'][$game->id()]['score_team_2'] = array(
        '#type' => 'textfield',
        '#size' => '5',
      );
    }
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Send'),
      '#button_type' => 'primary',
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $fid = $form_state->getValue('imported_file')[0];
    $form_state->setRedirect('mespronos.importer_start',['fid'=>$fid]);
  }

}
?>