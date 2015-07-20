<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\Controller\LeagueListController.
 */

namespace Drupal\mespronos\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;

/**
 * Provides a list controller for League entity.
 *
 * @ingroup mespronos
 */
class LeagueListController extends EntityListBuilder {
  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = t('ID');
    $header['sport'] = t('Sport');
    $header['name'] = t('Nom');
    $header['status'] = t('Statut');
    $header['classement'] = t('Classement activé ?');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\mespronos\Entity\League */
    $sport = $entity->getSport();
    $row['id'] = $entity->id();
    $row['sport'] = $sport->label();
    $row['name'] = \Drupal::l(
      $this->getLabel($entity),
      new Url(
        'entity.league.edit_form', array(
          'league' => $entity->id(),
        )
      )
    );
    $row['status'] = $entity->getStatus(false);
    $row['classement'] = $entity->hasClassement() ? '✓' : '✗';
    return $row + parent::buildRow($entity);
  }

}
