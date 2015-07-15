<?php

/**
 * @file
 * Contains game.page.inc.
 *
 * Game page callback file for the game entity.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for game templates.
 *
 * Default template: game.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_game(array &$variables) {
  // Fetch Game Entity Object.
  $game = $variables['elements']['#game'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
