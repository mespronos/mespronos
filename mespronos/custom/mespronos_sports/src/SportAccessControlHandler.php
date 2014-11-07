<?php

/**
 * @file
 * Contains Drupal\account\SportAccessController.
 */

namespace Drupal\mespronos_sports;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Sport entity.
 *
 * @see \Drupal\mespronos_sports\Entity\Sport.
 */
class SportAccessControlHandler extends EntityAccessControlHandler
{

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view Sport entity');
        break;

      case 'edit':
        return AccessResult::allowedIfHasPermission($account, 'edit Sport entity');
        break;

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete Sport entity');
        break;

    }

    return AccessResult::allowed();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add Bar entity');
  }
}
