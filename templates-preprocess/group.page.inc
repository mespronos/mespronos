<?php

use Drupal\Core\Render\Element;

function template_preprocess_group(&$variables) {
  $variables['group'] = $variables['elements']['#group'];
  $variables['attributes']['class'][] = 'group';
  $variables['attributes']['class'][] = 'group-'.$variables['elements']['#view_mode'];
  $variables['attributes']['id'] = 'team-'.$variables['group']->id();
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}