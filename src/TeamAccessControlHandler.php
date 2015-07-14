<?php

/**
 * @file
 * Contains Drupal\mespronos\TeamAccessControlHandler.
 */

namespace Drupal\mespronos;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Team entity.
 *
 * @see \Drupal\mespronos\Entity\Team.
 */
class TeamAccessControlHandler extends EntityAccessControlHandler {
  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view Team entity');

      case 'edit':
        return AccessResult::allowedIfHasPermission($account, 'edit Team entity');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete Team entity');
    }

    return AccessResult::allowed();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add Team entity');
  }

}
