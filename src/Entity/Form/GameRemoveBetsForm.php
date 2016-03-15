<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\Form\GameRemoveBetsForm.
 */

namespace Drupal\mespronos\Entity\Form;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a form for deleting bets on given game.
 *
 * @ingroup mespronos
 */
class GameRemoveBetsForm extends ContentEntityConfirmFormBase {
  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete bets on game %name ?', array('%name' => $this->entity->label()));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.game.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete bets');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $nb_bets_deleted = $this->entity->removeBets();

    drupal_set_message(
      $this->t('@nb_bets deleted',['@nb_bets' => $nb_bets_deleted])
    );

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
