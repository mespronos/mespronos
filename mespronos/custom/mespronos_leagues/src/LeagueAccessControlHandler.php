<?php

/**
 * @file
 * Contains Drupal\account\LeagueAccessController.
 */

namespace Drupal\mespronos_leagues;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the League entity.
 *
 * @see \Drupal\mespronos_leagues\Entity\League.
 */
class LeagueAccessControlHandler extends EntityAccessControlHandler
{

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view League entity');
        break;

      case 'edit':
        return AccessResult::allowedIfHasPermission($account, 'edit League entity');
        break;

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete League entity');
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
