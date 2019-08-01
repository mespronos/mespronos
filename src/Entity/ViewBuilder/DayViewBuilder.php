<?php
namespace Drupal\mespronos\Entity\ViewBuilder;

use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\mespronos\Controller\GameController;

class DayViewBuilder extends EntityViewBuilder {

  public function buildComponents(array &$build, array $entities, array $displays, $view_mode) {
    /** @var \Drupal\node\NodeInterface[] $entities */
    if (empty($entities)) {
      return;
    }
    parent::buildComponents($build, $entities, $displays, $view_mode);

    foreach ($entities as $id => $entity) {
      $bundle = $entity->bundle();
      /** @var \Drupal\Core\Entity\Entity\EntityViewDisplay $display */
      $display = $displays[$bundle];
      if ($display->getComponent('results_and_ranking')) {
        $build[$id]['results_and_ranking'] = \Drupal\mespronos\Controller\DayController::getResultsAndRankings($entity);
      }
    }
  }

}