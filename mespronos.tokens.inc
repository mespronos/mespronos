<?php

/**
 * @file
 * Builds placeholder replacement tokens for node-related data.
 */

use Drupal\Core\Render\BubbleableMetadata;
use Drupal\mespronos\Entity\Day;
use Drupal\mespronos\Entity\Game;
use Drupal\mespronos\Entity\League;

/**
 * Implements hook_token_info().
 */
function mespronos_token_info() {
  return [
    'types' => [
      'league' => [
        'name' => t('League'),
        'description' => t('Tokens related to a league.'),
      ],
      'game' => [
        'name' => t('Game'),
        'description' => t('Tokens related to a game.'),
      ],
      'day' => [
        'name' => t('Day'),
        'description' => t('Tokens related to a day.'),
      ],
    ],
    'tokens' => [
      'league' => _mespronos_token_info_league(),
      'game' => _mespronos_token_info_game(),
      'day' => _mespronos_token_info_day(),
    ],
  ];
}

function _mespronos_token_info_league() {
  $tokens = [
    'id' => [
      'name' => t("League ID"),
      'description' => t('The League ID.'),
    ],
    'name' => [
      'name' => t("League name"),
    ],
    'sport' => [
      'name' => t("Sport name"),
    ],
  ];
  return $tokens;
}

function _mespronos_token_info_game() {
  $tokens = [
    'id' => [
      'name' => t("League ID"),
    ],
    'name' => [
      'name' => t("Game label"),
    ],
    'league' => [
      'name' => t("League label"),
    ],
    'day' => [
      'name' => t("Day label"),
    ],
  ];
  return $tokens;
}

function _mespronos_token_info_day() {
  $tokens = [
    'id' => [
      'name' => t("Day ID"),
    ],
    'name' => [
      'name' => t("Day label"),
    ],
    'league' => [
      'name' => t("League label"),
    ],
  ];
  return $tokens;
}

/**
 * Implements hook_tokens().
 */
function mespronos_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $replacements = [];
  if ($type == 'entity' && isset($data['entity_type'])) {
    switch ($data['entity_type']) {
      case 'league' :
        _mespronos_tokens_league($tokens, $data['entity'],$replacements);
        break;
      case 'game' :
        _mespronos_tokens_game($tokens, $data['entity'],$replacements);
        break;
      case 'day' :
        _mespronos_tokens_day($tokens, $data['entity'],$replacements);
        break;
    }
  }
  return $replacements;
}

function _mespronos_tokens_league($tokens, League $league,&$replacements) {
  foreach ($tokens as $name => $original) {
    switch ($name) {
      case 'id':
        $replacements[$original] = $league->id();
        break;
      case 'name':
        $replacements[$original] = $league->getTheName();
        break;
      case 'sport':
        $replacements[$original] = $league->getSport()->label();
        break;
    }
  }
}

function _mespronos_tokens_game($tokens, Game $game,&$replacements) {
  foreach ($tokens as $name => $original) {
    switch ($name) {
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
    }
  }
}

function _mespronos_tokens_day($tokens, Day $day,&$replacements) {
  foreach ($tokens as $name => $original) {
    switch ($name) {
      case 'id':
        $replacements[$original] = $day->id();
        break;
      case 'name':
        $replacements[$original] = $day->label();
        break;
      case 'league':
        $league = $day->getLeague();
        $replacements[$original] = $league->label();
        break;
    }
  }
}