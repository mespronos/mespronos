<?php

namespace Drupal\mespronos_group\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mespronos_group\Entity\Group;
use Drupal\Core\Url;
use Drupal\Core\Cache\Cache;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\user\Entity\User;

/**
 * Form controller for Group edit forms.
 *
 * @ingroup mespronos_group
 */
class GroupForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\mespronos_group\Entity\Group */
    $form = parent::buildForm($form, $form_state);
    $rederer = \Drupal::service('renderer');

    $warning_vars = ['#theme' => 'group-form-warning'];

    $form['warning'] = [
      '#type' => 'markup',
      '#markup' => $rederer->renderPlain($warning_vars),
      '#weight' => -10,
    ];

    $form['autojoin'] = [
      '#type' => 'checkbox',
      '#title' => t('join the group'),
      '#weight' => 20,
    ];

    if(!\Drupal::currentUser()->hasPermission('choose to join group')) {
      $form['autojoin']['#default_value'] = true;
      $form['autojoin']['#access'] = false;
    }

    $form['user_id']['#access'] = false;
    $form['field_group_logo']['widget'][0]['#description'] = null;
    $id = $this->entity->id();
    if(isset($id) && $id>0) {
      $form['actions']['submit']['#value'] = t('Edit my group');

    }
    else {
      $form['actions']['submit']['#value'] = t('Create my group !');
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        if($form_state->getValue('autojoin')) {
          $user = \Drupal::currentUser();
          $user = User::load($user->id());
          $usergroups = $user->get("field_group")->getValue();
          $usergroups[] = [
            'target_id' => $entity->id()
          ];
          $user->set("field_group", $usergroups);
          $user->save();
        }
        drupal_set_message($this->t('You\'ve just created the %label Group.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Group.', [
          '%label' => $entity->label(),
        ]));
    }
    $url = new Url('entity.group.canonical',['group'=>$entity->id()]);
    $form_state->setRedirectUrl($url);
  }

}
