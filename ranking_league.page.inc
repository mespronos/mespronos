<?php

/**
 * @file
 * Contains ranking_league.page.inc.
 *
 * RankingLeague page callback file for the ranking_league entity.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for ranking_league templates.
 *
 * Default template: ranking_league.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_ranking_league(array &$variables) {
  // Fetch RankingLeague Entity Object.
  $ranking_league = $variables['elements']['#ranking_league'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
