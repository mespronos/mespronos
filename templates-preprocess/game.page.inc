<?php

use Drupal\Core\Render\Element;

function template_preprocess_game(&$variables) {
  $variables['is_score_setted'] = $variables['elements']['#game']->isScoreSetted();
  $variables['game'] = $variables['elements']['#game'];
}