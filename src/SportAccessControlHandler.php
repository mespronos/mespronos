<?php

/**
 * @file
 * Contains Drupal\mespronos\SportAccessControlHandler.
 */

namespace Drupal\mespronos;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Sport entity.
 *
 * @see \Drupal\mespronos\Entity\Sport.
 */
class SportAccessControlHandler extends EntityAccessControlHandler {
  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view Sport entity');

      case 'edit':
        return AccessResult::allowedIfHasPermission($account, 'edit Sport entity');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete Sport entity');
    }

    return AccessResult::allowed();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add Sport entity');
  }

}
