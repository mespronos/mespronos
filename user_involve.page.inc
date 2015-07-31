<?php

/**
 * @file
 * Contains user_involve.page.inc.
 *
 * UserInvolve page callback file for the user_involve entity.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for user_involve templates.
 *
 * Default template: user_involve.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_user_involve(array &$variables) {
  // Fetch UserInvolve Entity Object.
  $user_involve = $variables['elements']['#user_involve'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
