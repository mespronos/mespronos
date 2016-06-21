<?php

use Drupal\Core\Render\Element;

function template_preprocess_day(&$variables) {
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}