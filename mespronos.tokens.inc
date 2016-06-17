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
function mespronos_token_info() {
  $type = array(
    'name' => t('League'),
    'description' => t('Tokens related to a league.'),
    'needs-data' => 'node',
  );

  $tokens['id'] = array(
    'name' => t("League ID"),
    'description' => t('The League ID.'),
  );

  $tokens['name'] = array(
    'name' => t("League name"),
  );

  $tokens['sport'] = array(
    'name' => t("Sport name"),
  );

  $tokens['creator'] = array(
    'name' => t("group creator"),
    'type' => 'user',
  );

  return array(
    'types' => array('league' => $type),
    'tokens' => array('league' => $tokens),
  );
}

/**
 * Implements hook_tokens().
 */
function mespronos_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $token_service = \Drupal::token();
  
  $replacements = array();
  if ($type == 'entity' && isset($data['entity_type']) && $data['entity_type'] == 'league') {
    /** @var \Drupal\mespronos\Entity\League $league */
    $league = $data['entity'];

    foreach ($tokens as $name => $original) {
      switch ($name) {
        // Simple key values on the node.
        case 'id':
          $replacements[$original] = $league->id();
          break;

        case 'name':
          $replacements[$original] = $league->getTheName();
          break;

        case 'sport':
          $replacements[$original] = $league->getSport()->label();
          break;

        // Default values for the chained tokens handled below.
        case 'creator':
          $account = $league->getOwner() ? $league->getOwner() : User::load(0);
          $bubbleable_metadata->addCacheableDependency($account);
          $replacements[$original] = $account->label();
          break;
      }
    }

    if ($author_tokens = $token_service->findWithPrefix($tokens, 'author')) {
      $replacements += $token_service->generate('user', $author_tokens, array('user' => $league->getOwner()), $options, $bubbleable_metadata);
    }
  }

  return $replacements;
}
