<?php

/**
 * @file
 * Contains \Drupal\mespronos_registration\Form\MespronosLoginForm.
 */

namespace Drupal\mespronos_registration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class MespronosLoginForm.
 *
 * @package Drupal\mespronos_registration\Form
 */
class MespronosLoginForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mespronos_login_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
