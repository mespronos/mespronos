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

    $form['reminder']['reminder_hour'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Number of hours for notifications'),
      '#description' => $this->t(''),
      '#default_value' => $this->config('mespronos.reminder')->get('hours'),
      '#states' => [
        'required' => [':input[name="reminder_enabled"]' => ['checked' => true]],
        'visible' =>[':input[name="reminder_enabled"]' => ['checked' => true]],
      ]
    ];

    return parent::buildForm($form, $form_state);
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('reminder_hour') <= 0) {
      $form_state->setErrorByName('reminder_hour', $this->t("Can't be less or equal to 0."));
    }
    if (intval($form_state->getValue('reminder_hour')) != $form_state->getValue('reminder_hour')) {
      $form_state->setErrorByName('reminder_hour', $this->t("Should be a integer"));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('mespronos.reminder')
      ->set('enabled', (string) $form_state->getValue('reminder_enabled'))
      ->set('hours', (string) $form_state->getValue('reminder_hour'))
      ->save(TRUE);
  }
}