<?php

namespace Drupal\mespronos\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

class AdministrationConfigForm extends ConfigFormBase {

  public function getFormId() {
    return 'administration_config_form';
  }

  protected function getEditableConfigNames() {
    return [
      'mespronos.reminder'
    ];
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['reminder'] = [
      '#type' => 'fieldset',
      '#title' => t('Reminder'),
    ];

    $form['reminder']['reminder_enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable reminder notifications'),
      '#description' => $this->t(''),
      '#default_value' => $this->config('mespronos.reminder')->get('enabled'),
    ];

    $form['reminder']['reminders_hours'] = [
      '#title' => $this->t('Reminder to offer to users'),
      '#description' => $this->t('Note that user will only receive the reminder he choose.'),
      '#type' => 'number',
      '#min' => 0,
      '#step' => 1,
      '#size' => '1',
      '#default_value' => $this->config('mespronos.reminder')->get('hours'),
      '#states' => [
        'required' => [':input[name="reminder_enabled"]' => ['checked' => TRUE]],
        'visible' =>[':input[name="reminder_enabled"]' => ['checked' => TRUE]],
      ]
    ];

    $form['reminder']['reminder_hours_gap'] = [
      '#title' => $this->t('Number of hours between two reminders for the same day'),
      '#type' => 'number',
      '#min' => 0,
      '#step' => 1,
      '#size' => 1,
      '#default_value' => $this->config('mespronos.reminder')->get('hours_gap'),
      '#states' => [
        'required' => [':input[name="reminder_enabled"]' => ['checked' => TRUE]],
        'visible' =>[':input[name="reminder_enabled"]' => ['checked' => TRUE]],
      ]
    ];

    return parent::buildForm($form, $form_state);
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $hours = $form_state->getValue('reminders_hours');
    if ($form_state->getValue('reminder_enabled') && $hours <= 0) {
      $form_state->setErrorByName('reminder_hour', $this->t("Hour should be a positive number"));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('mespronos.reminder')
      ->set('enabled', (string) $form_state->getValue('reminder_enabled'))
      ->set('hours', $form_state->getValue('reminders_hours'))
      ->set('hours_gap', $form_state->getValue('reminder_hours_gap'))
      ->save(TRUE);
  }

}