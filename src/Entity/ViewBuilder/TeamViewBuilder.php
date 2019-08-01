<?php
namespace Drupal\mespronos\Entity\ViewBuilder;

use Drupal\Core\Entity\EntityViewBuilder;

class TeamViewBuilder extends EntityViewBuilder {

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
      if ($display->getComponent('last_results')) {
        $build[$id]['last_results'] = \Drupal\mespronos\Controller\TeamController::getLastResults($entity);
      }
    }
  }

}