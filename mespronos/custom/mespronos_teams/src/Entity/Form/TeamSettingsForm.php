<?php

/**
 * @file
 * Contains Drupal\mespronos_teams\Entity\Form\TeamSettingsForm.
 */

namespace Drupal\mespronos_teams\Entity\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class TeamSettingsForm.
 * @package Drupal\mespronos_teams\Form
 * @ingroup mespronos_teams
 */
class TeamSettingsForm extends FormBase
{

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'Team_settings';
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
   * Define the form used for Team  settings.
   * @return array
   *   Form definition array.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param array $form_state
   *   An associative array containing the current state of the form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['Team_settings']['#markup'] = 'Settings form for Team. Manage field settings here.';
    return $form;
  }
}
