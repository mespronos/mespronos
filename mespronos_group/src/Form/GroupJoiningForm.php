<?php

namespace Drupal\mespronos_group\Form;

use Drupal\Core\Form\FormBase;
use Drupal\user\Entity\User;

use Drupal\Core\Form\FormStateInterface;
use Drupal\mespronos_group\Entity\Group;
use Drupal\Core\Url;
use Drupal\Core\Cache\Cache;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Form controller for Group joining forms.
 *
 * @ingroup mespronos_group
 */
class GroupJoiningForm extends FormBase {

  public function getFormId() {
    return 'group_joining';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $group = $this->extractGroup($form_state);
    $render_controller = \Drupal::entityManager()->getViewBuilder('group');
    $form['group'] = [
      '#markup' => render($render_controller->view($group,'teaser')),
    ];

    $user = \Drupal::currentUser();
    $user = User::load($user->id());
    if($group->isMemberOf($user)) {
      drupal_set_message(t('You are already part of %group_name group',['%group_name'=>$group->label()]));
      return new RedirectResponse(\Drupal::url('entity.group.canonical',['group'=>$group->id()]));
    }
    $form['access_code'] = [
      '#title' => t('Access code'),
      '#type' => 'textfield',
      '#description' => t('This group is private, you need an access code to get in.')
    ];
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Join group'),
      '#button_type' => 'primary',
    );
    return $form;
  }


  public function validateForm(array &$form, FormStateInterface $form_state) {
    $code = $form_state->getValue('access_code');
    $group = $this->extractGroup($form_state);
    if($code != $group->getCode()) {
      $form_state->setErrorByName('access_code', $this->t("The access code is wrong"));
    }
  }
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $group = $this->extractGroup($form_state);
    $user = \Drupal::currentUser();
    $user = User::load($user->id());
    $usergroups = $user->get("field_group")->getValue();
    $usergroups[] = [
      'target_id' => $group->id()
    ];
    $user->set("field_group", $usergroups);
    $user->save();
    Cache::invalidateTags(array('groups','ranking'));
    drupal_set_message(t('You are now part of the group %group_name',['%group_name'=>$group->label()]));
    $url = new Url('entity.group.canonical',['group'=>$group->id()]);
    $form_state->setRedirectUrl($url);
  }
  /**
   * @param $form_state
   * @return Group
   */
  protected function extractGroup(FormStateInterface $form_state) {
    return $form_state->getBuildInfo()['args'][0];
  }
}
