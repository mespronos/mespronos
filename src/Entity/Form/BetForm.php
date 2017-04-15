<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\Form\BetForm.
 */

namespace Drupal\mespronos\Entity\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the Bet entity edit forms.
 *
 * @ingroup mespronos
 */
class BetForm extends ContentEntityForm {

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::save().
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $status = $entity->save();

    if ($status) {
      drupal_set_message($this->t('Saved the %label Bet.', array(
        '%label' => $entity->label(),
      )));
    } else {
      drupal_set_message($this->t('The %label Bet was not saved.', array(
        '%label' => $entity->label(),
      )));
    }
    $form_state->setRedirect('entity.bet.edit_form', ['bet' => $entity->id()]);
  }

}
