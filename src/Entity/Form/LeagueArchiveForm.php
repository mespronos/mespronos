<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\Form\LeagueArchiveForm.
 */

namespace Drupal\mespronos\Entity\Form;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a form for archive a league.
 *
 * @ingroup mespronos
 */
class LeagueArchiveForm extends ContentEntityConfirmFormBase {
  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to archive the league %name ?', ['%name' => $this->entity->label()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.league.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Archive league');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->close();

    drupal_set_message(
      $this->t('League %name has been archived', ['%name' => $this->entity->label()])
    );

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
