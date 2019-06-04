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
      '#prefix' => '<section class="large-12 columns block-content"><div class="content"><div class="inner">',
    ];

    $form['autojoin'] = [
      '#type' => 'checkbox',
      '#title' => t('join the group'),
      '#weight' => 20,
    ];

    if (!\Drupal::currentUser()->hasPermission('choose to join group')) {
      $form['autojoin']['#default_value'] = FALSE;
      $form['autojoin']['#access'] = FALSE;
    }

    if (\Drupal::moduleHandler()->moduleExists('domain') && !\Drupal::currentUser()->hasPermission('affect domain group')) {
      $form['domain']['#access'] = FALSE;
    }

    $form['user_id']['#access'] = FALSE;
    $form['field_group_logo']['widget'][0]['#description'] = NULL;
    $id = $this->entity->id();
    if (isset($id) && $id > 0) {
      $form['actions']['submit']['#value'] = t('Modifier mon groupe');

    } else {
      $form['actions']['submit']['#value'] = t('Create my group !');
    }

    $form['actions']['#suffix'] = '</div></div></section>';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var Group $entity */
    $entity = $this->entity;
    $status = parent::save($form, $form_state);

    $user = \Drupal::currentUser();
    $user = User::load($user->id());
    switch ($status) {
      case SAVED_NEW:
        if ($form_state->getValue('autojoin')) {
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

        $url = new Url('entity.group.canonical', ['group' => $entity->id()]);
        $form_state->setRedirectUrl($url);

        break;

      default:
        drupal_set_message($this->t('Saved the %label Group.', [
          '%label' => $entity->label(),
        ]));
    }

    $this->sendMail($user, $entity, $status);

  }

  private function sendMail(User $user, Group $group, $status) {
    $mailManager = \Drupal::service('plugin.manager.mail');

    $build['#theme'] = 'email-new-group';
    $build['#group'] = [
      'name' => $group->label(),
      'id' => $group->id(),
      'code' => $group->getCode(),
    ];
    $build['#user'] = [
      'name' => $user->getDisplayName()
    ];
    $rederer = \Drupal::service('renderer');

    $params['message'] = $rederer->renderPlain($build);
    if ($status === SAVED_NEW) {
      $params['subject'] = t('MesPronos - Groupe @group créé !', [
        '@group' => $group->label()
      ]);
    }
    else {
      $params['subject'] = t('MesPronos - Groupe @group modifié !', [
        '@group' => $group->label()
      ]);
    }

    $mailManager->mail('mespronos', 'group', $user->getEmail(), $user->getPreferredLangcode(), $params, NULL, TRUE);
  }

  private function getEmail(Group $group) {
  }

}
