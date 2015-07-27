<?php

/**
 * @file
 * Contains ranking_day.page.inc.
 *
 * RankingDay page callback file for the ranking_day entity.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for ranking_day templates.
 *
 * Default template: ranking_day.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_ranking_day(array &$variables) {
  // Fetch RankingDay Entity Object.
  $ranking_day = $variables['elements']['#ranking_day'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
