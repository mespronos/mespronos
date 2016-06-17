<?php

/**
 * @file
 * Builds placeholder replacement tokens for node-related data.
 */

use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\user\Entity\User;

/**
 * Implements hook_token_info().
 */
function mespronos_group_token_info() {
  $type = array(
    'name' => t('Groups'),
    'description' => t('Tokens related to groups.'),
    'needs-data' => 'node',
  );

  $group['gid'] = array(
    'name' => t("Group ID"),
    'description' => t('The unique ID of the group.'),
  );

  $group['name'] = array(
    'name' => t("Group name"),
  );

  $group['created'] = array(
    'name' => t("Date created"),
    'type' => 'date',
  );

  $group['changed'] = array(
    'name' => t("Date changed"),
    'description' => t("The date the group was most recently updated."),
    'type' => 'date',
  );

  $group['creator'] = array(
    'name' => t("group creator"),
    'type' => 'user',
  );

  return array(
    'types' => array('group' => $type),
    'tokens' => array('group' => $group),
  );
}

/**
 * Implements hook_tokens().
 */
function mespronos_group_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $token_service = \Drupal::token();
  
  $replacements = array();
  if ($type == 'entity' && isset($data['entity_type']) && $data['entity_type'] == 'group') {
    /** @var \Drupal\mespronos_group\Entity\Group $group */
    $group = $data['entity'];

    foreach ($tokens as $name => $original) {
      switch ($name) {
        // Simple key values on the node.
        case 'gid':
          $replacements[$original] = $group->id();
          break;

        case 'name':
          $replacements[$original] = $group->getTheName();
          break;

        // Default values for the chained tokens handled below.
        case 'creator':
          $account = $group->getOwner() ? $group->getOwner() : User::load(0);
          $bubbleable_metadata->addCacheableDependency($account);
          $replacements[$original] = $account->label();
          break;
      }
    }

    if ($author_tokens = $token_service->findWithPrefix($tokens, 'author')) {
      $replacements += $token_service->generate('user', $author_tokens, array('user' => $group->getOwner()), $options, $bubbleable_metadata);
    }
  }

  return $replacements;
}
