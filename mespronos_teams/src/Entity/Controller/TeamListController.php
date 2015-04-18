<?php

/**
 * @file
 * Contains Drupal\mespronos_teams\Entity\Controller\TeamListController.
 */

namespace Drupal\mespronos_teams\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;

/**
 * Provides a list controller for Team entity.
 *
 * @ingroup mespronos_teams
 */
class TeamListController extends EntityListBuilder {
  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = t('TeamID');
    $header['name'] = t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\mespronos_teams\Entity\Team */
    $row['id'] = $entity->id();
    $row['name'] = \Drupal::l(
      $this->getLabel($entity),
      new Url(
        'entity.team.edit_form', array(
          'team' => $entity->id(),
        )
      )
    );
    return $row + parent::buildRow($entity);
  }

}
