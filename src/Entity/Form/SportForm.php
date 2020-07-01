<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\Form\SportForm.
 */

namespace Drupal\mespronos\Entity\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the Sport entity edit forms.
 *
 * @ingroup mespronos
 */
class SportForm extends ContentEntityForm {
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
    $query = \Drupal::entityQuery('sport')->condition('name', '%' . $entity->get('name')->value . '%', 'LIKE');
    $id = $query->execute();

    if (count($id) === 0) {
      if ($entity->save()) {
        $this->messenger()->addStatus($this->t('Saved the %label Sport.', ['%label' => $entity->label()]));
      }
      else {
        $this->messenger()->addStatus($this->t('The %label Sport was not saved.', ['%label' => $entity->label()]));
      }
    }
    else {
      $this->messenger()->addStatus($this->t('The %label Sport was not saved as it already exist.', ['%label' => $entity->label()]));
    }
    $form_state->setRedirect('entity.sport.collection');
  }

}
