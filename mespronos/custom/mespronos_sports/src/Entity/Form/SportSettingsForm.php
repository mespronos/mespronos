<?php

/**
 * @file
 * Contains Drupal\mespronos_sports\Entity\Form\SportSettingsForm.
 */

namespace Drupal\mespronos_sports\Entity\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SportSettingsForm.
 * @package Drupal\mespronos_sports\Form
 * @ingroup mespronos_sports
 */
class SportSettingsForm extends FormBase
{

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'Sport_settings';
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param array $form_state
   *   An associative array containing the current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Empty implementation of the abstract submit class.
  }


  /**
   * Define the form used for Sport  settings.
   * @return array
   *   Form definition array.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param array $form_state
   *   An associative array containing the current state of the form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['Sport_settings']['#markup'] = 'Settings form for Sport. Manage field settings here.';
    return $form;
  }
}
