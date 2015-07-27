<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\Form\RankingDaySettingsForm.
 */

namespace Drupal\mespronos\Entity\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class RankingDaySettingsForm.
 *
 * @package Drupal\mespronos\Form
 *
 * @ingroup mespronos
 */
class RankingDaySettingsForm extends FormBase {
  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'RankingDay_settings';
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Empty implementation of the abstract submit class.
  }


  /**
   * Define the form used for RankingDay  settings.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   Form definition array.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['RankingDay_settings']['#markup'] = 'Settings form for RankingDay. Manage field settings here.';
    return $form;
  }

}
