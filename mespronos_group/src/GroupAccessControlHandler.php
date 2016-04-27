<?php

namespace Drupal\mespronos_group;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\user\Entity\User;

/**
 * Access controller for the Group entity.
 *
 * @see \Drupal\mespronos_group\Entity\Group.
 */
class GroupAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\mespronos_group\GroupInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {;
          return AccessResult::allowedIfHasPermission($account, 'view unpublished group entities');
        }
        $user = User::load($account->id());
        if($entity->isMemberOf($user)) {
          return AccessResult::allowed();
        }
      
      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit group entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete group entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add group entities');
  }

}
