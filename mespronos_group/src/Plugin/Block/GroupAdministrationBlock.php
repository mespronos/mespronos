<?php

namespace Drupal\mespronos_group\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\mespronos_group\Entity\Group;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Provides a 'GroupAdministrationBlock' block.
 *
 * @Block(
 *  id = "group_administration_block",
 *  admin_label = @Translation("Group administration block"),
 * )
 */
class GroupAdministrationBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    if (\Drupal::routeMatch()->getRouteName() == 'entity.group.canonical') {
      /* @var $group Group */
      $group = \Drupal::routeMatch()->getParameter('group');
      $group_data = [];
      $group_data['nb_members'] = $group->getMemberNumber();
      $group_data['name'] = $group->label();
      $group_data['access_code'] = $group->getCode();
      $group_data['url_join'] = Link::fromTextAndUrl(Url::fromRoute('mespronos_group.group.join',['group'=>$group->id()],['absolute'=>true])->toString(),Url::fromRoute('mespronos_group.group.join',['group'=>$group->id()]));
      $group_data['url_leave'] = Link::fromTextAndUrl(t('Leave group'),Url::fromRoute('mespronos_group.group.leave',['group'=>$group->id()]));

      $creator = \Drupal\user\Entity\User::load($group->getOwner()->id());
      $group_data['creator'] = [
        'id' => $creator->id(),
        'name' => $creator->getAccountName(),
      ];

      $build = [];
      $build['group_members_block'] = [
        '#theme' => 'group-administration',
        '#group' => $group_data,
      ];
      $build['#cache'] = [
        'contexts' => ['url'],
        'tags' => ['group:'.$group->id()],
      ];

      $build['#title'] = t('Group "@group_name"',['@group_name'=>$group_data['name']]);
      return $build;
    }
    return [];
  }

}
