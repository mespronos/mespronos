<?php

/**
 * @file
 * Builds placeholder replacement tokens for node-related data.
 */

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

  return array(
    'types' => array('group' => $type),
    'tokens' => array('group' => $group),
  );
}

/**
 * Implements hook_tokens().
 */
function mespronos_group_tokens($type, $tokens, array $data) {
  $replacements = array();
  if ($type == 'entity' && isset($data['entity_type'])) {
    switch ($data['entity_type']) {
      case 'group' :
        _mespronos_group_tokens_group($tokens, $data['entity'], $replacements);
        break;
    }
  }
  return $replacements;
}

function _mespronos_group_tokens_group($tokens, Drupal\mespronos_group\Entity\Group $group, &$replacements) {
  foreach ($tokens as $name => $original) {
    switch ($name) {
      case 'gid':
        $replacements[$original] = $group->id();
        break;
      case 'name':
        $replacements[$original] = $group->getTheName();
        break;
    }
  }
}