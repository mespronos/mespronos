<?php
namespace Drupal\mespronos\Entity\ViewBuilder;

use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\mespronos\Controller\GameController;

class GameViewBuilder extends EntityViewBuilder {

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
      if ($display->getComponent('better_bets')) {
        $build[$id]['betters_bets'] = GameController::getBettersBets($entity);
      }
      if ($display->getComponent('flags')) {
        $build[$id]['flags'] = GameController::getTeamFlags($entity);
      }
    }
  }

}