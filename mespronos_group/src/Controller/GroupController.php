<?php

namespace Drupal\mespronos_group\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\mespronos_group\Entity\Group;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class GroupController.
 *
 * @package Drupal\mespronos_group\Controller
 */
class GroupController extends ControllerBase {

  /**
   * List.
   *
   * @return string
   *   Return Hello string.
   */
  public function groupList() {
    $groups = self::loadGroups(true);
    $groups = self::parseGroupsForListing($groups);
    $user = \Drupal::currentUser();
    $user = User::load($user->id());
    $user_groups = Group::getUserGroup($user);
    if ($user_groups && count($user_groups) > 0) {
      $user_groups = self::parseGroupsForListing($user_groups);
    } else {
      $user_groups = [];
    }
    return [
      '#theme' => 'group-list',
      '#groups' => $groups,
      '#user_groups' => $user_groups,
      '#cache' => [
        'contexts' => ['user'],
        'tags' => ['user:'.$user->id(), 'groups'],
        'max-age' => '120',
      ],
    ];
  }

  public static function loadGroups($onlyActive = true, $onlyVisible = true) {
    $storage = \Drupal::entityTypeManager()->getStorage('group');
    $query = \Drupal::entityQuery('group');
    if ($onlyActive) {
      $query->condition('status', NODE_PUBLISHED);
    }
    if ($onlyVisible) {
      $query->condition('hidden', FALSE);
    }
    $query->sort('name');

    $ids = $query->execute();
    if (count($ids) > 0) {
      return $storage->loadMultiple($ids);
    } else {
      return [];
    }
  }

  /**
   * @param Group[] $groups
   * @return array
   */
  public static function parseGroupsForListing(&$groups) {
    $render_controller = \Drupal::entityTypeManager()->getViewBuilder('group');
    $user = \Drupal::currentUser();
    $groups_return = [];
    foreach ($groups as $group) {
      $groups_return[] = $render_controller->view($group, 'teaser');
    }
    return $groups_return;
  }

  public function joinTitle(Group $group) {
    return t('Join groupe %group_name', ['%group_name'=>$group->label()]);
  }

  public function join(Group $group) {
    $form = \Drupal::formBuilder()->getForm('Drupal\mespronos_group\Form\GroupJoiningForm', $group);
    return $form;
  }

  public function leaveTitle(Group $group) {
    return t('Leave groupe %group_name', ['%group_name'=>$group->label()]);
  }

  public function leave(Group $group) {
    $form = \Drupal::formBuilder()->getForm('Drupal\mespronos_group\Form\GroupLeavingForm', $group);
    return $form;
  }

}
