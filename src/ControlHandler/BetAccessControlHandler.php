<?php

/**
 * @file
 * Contains Drupal\mespronos\BetAccessControlHandler.
 */

namespace Drupal\mespronos\ControlHandler;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Bet entity.
 *
 * @see \Drupal\mespronos\Entity\Bet.
 */
class BetAccessControlHandler extends EntityAccessControlHandler {
  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'manage mespronos content');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'manage mespronos content');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'manage mespronos content');
    }

    return AccessResult::allowed();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add Bet entity');
  }

}
