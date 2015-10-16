<?php

/**
 * @file
 * Contains Drupal\mespronos\UserInvolveAccessControlHandler.
 */

namespace Drupal\mespronos;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the UserInvolve entity.
 *
 * @see \Drupal\mespronos\Entity\UserInvolve.
 */
class UserInvolveAccessControlHandler extends EntityAccessControlHandler {
  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    if(is_null($account)) {
      $account = User::load(\Drupal::currentUser()->id());
    }

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view UserInvolve entity');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit UserInvolve entity');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete UserInvolve entity');
    }

    return AccessResult::allowed();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add UserInvolve entity');
  }

}
