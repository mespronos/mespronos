<?php

use Drupal\Core\Render\Element;

function template_preprocess_league(&$variables) {
  $variables['league'] = $variables['elements']['#league'];
  $variables['attributes']['class'][] = 'league';
  $variables['attributes']['class'][] = 'league-'.$variables['elements']['#view_mode'];
  $variables['attributes']['id'] = 'league-'.$variables['league']->id();
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}