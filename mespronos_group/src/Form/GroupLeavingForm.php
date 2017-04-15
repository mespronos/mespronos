<?php

namespace Drupal\mespronos_group\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\mespronos_group\Entity\Group;
use Drupal\Core\Cache\Cache;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Provides a deletion confirmation form when deleting mespronos datas
 */
class GroupLeavingForm extends ConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to leave the group ?');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('mespronos_group.listing');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Leave');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormName() {
    return 'confirm';
  }

  public function getFormId() {
    return 'LeaveGroupForm';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    /** @var Group $group */
    $group = $form_state->getBuildInfo()['args'][0];
    $user = \Drupal::currentUser();
    $user = User::load($user->id());
    if (!$group->isMemberOf($user)) {
      drupal_set_message(t('You are not a member of of %group_name group', ['%group_name'=>$group->label()]));
      return new RedirectResponse($this->getCancelUrl());
    }
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var Group $group */
    $group = $form_state->getBuildInfo()['args'][0];

    $user = \Drupal::currentUser();
    $user = User::load($user->id());
    $usergroups = $user->get("field_group")->getValue();
    foreach ($usergroups as $key => $value) {
      if ($value['target_id'] == $group->id()) {
        unset($usergroups[$key]);
      }
    }
    $user->set("field_group", $usergroups);
    $user->save();
    Cache::invalidateTags(array('group:'.$group->id(), 'groups', 'ranking'));
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
