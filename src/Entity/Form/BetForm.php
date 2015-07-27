<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\Form\BetForm.
 */

namespace Drupal\mespronos\Entity\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\Language;

/**
 * Form controller for the Bet entity edit forms.
 *
 * @ingroup mespronos
 */
class BetForm extends ContentEntityForm {
  /**
   * Overrides Drupal\Core\Entity\EntityFormController::buildForm().
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\mespronos\Entity\Bet */
    $form = parent::buildForm($form, $form_state);
    $entity = $this->entity;

    $form['langcode'] = array(
      '#title' => $this->t('Language'),
      '#type' => 'language_select',
      '#default_value' => $entity->getUntranslated()->language()->getId(),
      '#languages' => Language::STATE_ALL,
    );

    return $form;
  }

  /**
   * Overrides \Drupal\Core\Entity\EntityFormController::submit().
   */
  public function submit(array $form, FormStateInterface $form_state) {
    // Build the entity object from the submitted values.
    $entity = parent::submit($form, $form_state);

    return $entity;
  }

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
    }
    else {
      drupal_set_message($this->t('The %label Bet was not saved.', array(
        '%label' => $entity->label(),
      )));
    }
    $form_state->setRedirect('entity.bet.edit_form', ['bet' => $entity->id()]);
  }

}
