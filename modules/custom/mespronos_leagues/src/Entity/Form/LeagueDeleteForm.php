<?php

/**
 * @file
 * Contains Drupal\mespronos_leagues\Entity\Form\LeagueDeleteForm.
 */

namespace Drupal\mespronos_leagues\Entity\Form;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a form for deleting a League entity.
 *
 * @ingroup mespronos_leagues
 */
class LeagueDeleteForm extends ContentEntityConfirmFormBase
{

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Are you sure you want to delete entity %name?', array('%name' => $this->entity->label()));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('league.list');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->delete();

    \Drupal::logger('mespronos_leagues')->notice('content @type: deleted %title.', array('@type' => $this->entity->bundle(), '%title' => $this->entity->label()));
    $form_state->setRedirectUrl($this->getCancelUrl());
  }
}
