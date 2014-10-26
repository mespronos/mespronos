<?php

/**
 * @file
 * Contains league.page.inc
 * League page callback file for the league entity.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for league templates.
 *
 * Default template: league.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */

function template_preprocess_league(&$variables) {
  // Fetch League Entity Object
  $league = $variables['elements']['#league'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
