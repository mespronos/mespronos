<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\Controller\TeamListController.
 */

namespace Drupal\mespronos\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;

/**
 * Provides a list controller for Team entity.
 *
 * @ingroup mespronos
 */
class TeamListController extends EntityListBuilder {
  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = t('ID');
    $header['name'] = t('Nom de l\'equipe');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\mespronos\Entity\Team */
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
