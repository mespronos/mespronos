<?php

/**
 * @file
 * Contains Drupal\mespronos\RankingDayAccessControlHandler.
 */

namespace Drupal\mespronos;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\user\Entity\User;

/**
 * Access controller for the RankingDay entity.
 *
 * @see \Drupal\mespronos\Entity\RankingDay.
 */
class RankingDayAccessControlHandler extends EntityAccessControlHandler {
  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    if(is_null($account)) {
      $account = User::load(\Drupal::currentUser()->id());
    }

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view RankingDay entity');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit RankingDay entity');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete RankingDay entity');
    }

    return AccessResult::allowed();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add RankingDay entity');
  }

}
