<?php

/**
 * @file
 * Contains Drupal\mespronos_leagues\Entity\Controller\LeagueListController.
 */

namespace Drupal\mespronos_leagues\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;

/**
 * Provides a list controller for League entity.
 *
 * @ingroup mespronos_leagues
 */
class LeagueListController extends EntityListBuilder {
  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = t('Id');
    $header['name'] = t('Nom');
    $header['status'] = t('Statut');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\mespronos_leagues\Entity\League */
    $row['id'] = $entity->id();
    $row['name'] = \Drupal::l(
      $this->getLabel($entity),
      new Url(
        'entity.league.edit_form', array(
          'league' => $entity->id(),
        )
      )
    );
    $row['status'] = $entity->getStatus(false);
    return $row + parent::buildRow($entity);
  }

}
