<?php

namespace Drupal\mespronos_group\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Group edit forms.
 *
 * @ingroup mespronos_group
 */
class GroupForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\mespronos_group\Entity\Group */
    $form = parent::buildForm($form, $form_state);
    $form['user_id']['#access'] = false;
    $entity = $this->entity;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Group.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Group.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.group.canonical', ['group' => $entity->id()]);
  }

}
