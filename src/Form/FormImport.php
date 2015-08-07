<?php

namespace Drupal\mespronos\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
/**
 * Implements an example form.
 */
class FormImport extends FormBase {

  /**
   * {@inheritdoc}.
   */
  public function getFormId() {
    return 'form_import';
  }

  /**
   * {@inheritdoc}.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['imported_file'] = array(
      '#type' => 'managed_file',
      '#title' => t('YAML file to import'),
      '#required' => true,
      '#upload_location' => 'public://imports/'.date('U'),
      '#upload_validators' => [
        'file_validate_extensions' => ['yaml']
      ]
    );
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Send'),
      '#button_type' => 'primary',
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $fid = $form_state->getValue('imported_file')[0];
    $form_state->setRedirect('mespronos.importer_start',['fid'=>$fid]);
  }

}
?>