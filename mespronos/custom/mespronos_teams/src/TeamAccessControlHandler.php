<?php

/**
 * @file
 * Contains Drupal\account\TeamAccessController.
 */

namespace Drupal\mespronos_teams;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Team entity.
 *
 * @see \Drupal\mespronos_teams\Entity\Team.
 */
class TeamAccessControlHandler extends EntityAccessControlHandler
{

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view Team entity');
        break;

      case 'edit':
        return AccessResult::allowedIfHasPermission($account, 'edit Team entity');
        break;

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete Team entity');
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
