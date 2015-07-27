<?php

/**
 * @file
 * Contains bet.page.inc.
 *
 * Bet page callback file for the bet entity.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for bet templates.
 *
 * Default template: bet.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_bet(array &$variables) {
  // Fetch Bet Entity Object.
  $bet = $variables['elements']['#bet'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
