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
      '#type' => 'checkboxes',
      '#title' => $this->t('Reminder to offer to users'),
      '#description' => $this->t('Note that user will only receive the reminder he choose.'),
      '#options' => [
        48 => t('48 hours'),
        36 => t('36 hours'),
        24 => t('24 hours'),
        12 => t('12 hours'),
        6 => t('6 hours'),
      ],
      '#default_value' => $this->config('mespronos.reminder')->get('hours'),
      '#states' => [
        'required' => [':input[name="reminder_enabled"]' => ['checked' => true]],
        'visible' =>[':input[name="reminder_enabled"]' => ['checked' => true]],
      ]
    ];

    return parent::buildForm($form, $form_state);
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $hours = self::parseReminderHours($form_state->getValue('reminders_hours'));
    if ($form_state->getValue('reminder_enabled') && count($hours) == 0) {
      $form_state->setErrorByName('reminder_hour', $this->t("You have to choose at least one option"));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $hours = self::parseReminderHours($form_state->getValue('reminders_hours'));
    $this->config('mespronos.reminder')
      ->set('enabled', (string) $form_state->getValue('reminder_enabled'))
      ->set('hours', $hours)
      ->save(TRUE);
  }

  public static function parseReminderHours($form_value) {
    $hours = [];
    foreach ($form_value as $key => $is_enabled) {
      if($is_enabled != 0) {
        $hours[$key] = $key;
      }
    }
    return $hours;
  }
}