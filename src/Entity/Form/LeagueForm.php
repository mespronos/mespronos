<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\Form\LeagueForm.
 */

namespace Drupal\mespronos\Entity\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the League entity edit forms.
 *
 * @ingroup mespronos
 */
class LeagueForm extends ContentEntityForm {
  /**
   * Overrides Drupal\Core\Entity\EntityFormController::buildForm().
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $form['creator']['#access'] = false;
    return $form;
  }


  /**
   * Overrides Drupal\Core\Entity\EntityFormController::save().
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $status = $entity->save();

    if ($status) {
      $this->messenger()->addStatus($this->t('Saved the %label League.', ['%label' => $entity->label()]));
    }
    else {
      $this->messenger()->addStatus($this->t('The %label League was not saved.', ['%label' => $entity->label()]));
    }
    $form_state->setRedirect('view.admin_leagues.page');
  }

}
