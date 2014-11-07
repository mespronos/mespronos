<?php

/**
 * @file
 * Contains Drupal\account\DayAccessController.
 */

namespace Drupal\mespronos_days;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Day entity.
 *
 * @see \Drupal\mespronos_days\Entity\Day.
 */
class DayAccessControlHandler extends EntityAccessControlHandler
{

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view Day entity');
        break;

      case 'edit':
        return AccessResult::allowedIfHasPermission($account, 'edit Day entity');
        break;

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete Day entity');
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
