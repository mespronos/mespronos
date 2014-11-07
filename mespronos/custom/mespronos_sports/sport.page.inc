<?php

/**
 * @file
 * Contains sport.page.inc
 * Sport page callback file for the sport entity.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for sport templates.
 *
 * Default template: sport.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */

function template_preprocess_sport(&$variables) {
  // Fetch Sport Entity Object
  $sport = $variables['elements']['#sport'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
