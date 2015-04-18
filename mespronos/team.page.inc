<?php

/**
 * @file
 * Contains team.page.inc.
 *
 * Team page callback file for the team entity.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for team templates.
 *
 * Default template: team.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_team(array &$variables) {
  // Fetch Team Entity Object.
  $team = $variables['elements']['#team'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
