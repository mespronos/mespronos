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
  $league_tokens['id'] = array(
    'name' => t("League ID"),
    'description' => t('The League ID.'),
  );

  $league_tokens['name'] = array(
    'name' => t("League name"),
  );

  $league_tokens['sport'] = array(
    'name' => t("Sport name"),
  );

  $league_tokens['creator'] = array(
    'name' => t("group creator"),
    'type' => 'user',
  );

  $game_tokens['id'] = array(
    'name' => t("Game ID"),
  );

  $game_tokens['name'] = array(
    'name' => t("Game label"),
    'description' => t('Example : "team a - team b".'),
  );

  $game_tokens['creator'] = array(
    'name' => t("game creator"),
    'type' => 'user',
  );

  $game_tokens['league'] = array(
    'name' => t("Games' league entity"),
  );

  $game_tokens['day'] = array(
    'name' => t("Games' Day entity"),
  );


  return [
    'types' => [
      'league' => [
        'name' => t('League'),
        'description' => t('Tokens related to a league.'),
        'needs-data' => 'node',
      ],
      'game' => [
        'name' => t('Game'),
        'description' => t('Tokens related to a game.'),
      ],
    ],
    'tokens' => [
      'league' => $league_tokens,
      'game' => $game_tokens
    ],
  ];
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
  if ($type == 'entity' && isset($data['entity_type']) && $data['entity_type'] == 'game') {
    /** @var \Drupal\mespronos\Entity\Game $game */
    $game = $data['entity'];

    foreach ($tokens as $name => $original) {
      switch ($name) {
        // Simple key values on the node.
        case 'id':
          $replacements[$original] = $game->id();
          break;
        case 'name':
          $replacements[$original] = $game->labelTeams();
          break;
        case 'day':
          $day = $game->getDay();
          $replacements[$original] = $day->label();
          break;
        case 'league':
          $league = $game->getLeague();
          $replacements[$original] = $league->label();
          break;
        case 'creator':
          $account = $game->getOwner() ? $game->getOwner() : User::load(0);
          $bubbleable_metadata->addCacheableDependency($account);
          $replacements[$original] = $account->label();
          break;
      }
    }
    if ($author_tokens = $token_service->findWithPrefix($tokens, 'author')) {
      $replacements += $token_service->generate('user', $author_tokens, array('user' => $game->getOwner()), $options, $bubbleable_metadata);
    }
  }

  return $replacements;
}
