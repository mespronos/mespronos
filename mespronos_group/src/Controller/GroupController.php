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
    $user_groups = self::parseGroupsForListing($user_groups);
    return [
      '#theme' => 'group-list',
      '#groups' => $groups,
      '#user_groups' => $user_groups,
      '#cache' => [
        'contexts' => ['user'],
        'tags' => [ 'user:'.$user->id(),'groups'],
        'max-age' => '120',
      ],
    ];
  }

  public static function loadGroups($onlyActive = true,$onlyVisible=true) {
    $storage = \Drupal::entityManager()->getStorage('group');
    $query =  \Drupal::entityQuery('group');
    if($onlyActive) {
      $query->condition('status',NODE_PUBLISHED);
    }
    if($onlyVisible) {
      $query->condition('hidden',FALSE);
    }
    $query->sort('name');

    $ids = $query->execute();
    if(count($ids)>0) {
      return $storage->loadMultiple($ids);
    }
    else {
      return [];
    }
  }

  /**
   * @param Group[] $groups
   * @return array
   */
  public static function parseGroupsForListing(&$groups) {
    $render_controller = \Drupal::entityManager()->getViewBuilder('group');
    $user = \Drupal::currentUser();
    $user = User::load($user->id());
    $groups_return = [];
    foreach ($groups as $group) {
      $groups_return[$group->id()] = [
        'entity' => $render_controller->view($group,'teaser'),
        'member' => t('@nb members',['@nb'=>$group->getMemberNumber()]),
        'is_member' => $group->isMemberOf($user),
        'display_join_link' => $user->id()>0,
        'join_url' => Url::fromRoute('mespronos_group.group.join',['group'=>$group->id()]),
      ];
    }
    return $groups_return;
  }

  public function joinTitle(Group $group) {
    return t('Join groupe %group_name',['%group_name'=>$group->label()]);
  }

  public function join(Group $group) {
    $form = \Drupal::formBuilder()->getForm('Drupal\mespronos_group\Form\GroupJoiningForm',$group);
    return $form;
  }

  public function leaveTitle(Group $group) {
    return t('Leave groupe %group_name',['%group_name'=>$group->label()]);
  }

  public function leave(Group $group) {
    $form = \Drupal::formBuilder()->getForm('Drupal\mespronos_group\Form\GroupLeavingForm',$group);
    return $form;
  }

}
