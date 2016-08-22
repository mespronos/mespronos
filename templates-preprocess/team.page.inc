<?php

use Drupal\Core\Render\Element;

function template_preprocess_team(&$variables) {
  $variables['team'] = $variables['elements']['#team'];
  $variables['attributes']['class'][] = 'team';
  $variables['attributes']['class'][] = 'team-'.$variables['elements']['#view_mode'];
  $variables['attributes']['id'] = 'team-'.$variables['team']->id();
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}