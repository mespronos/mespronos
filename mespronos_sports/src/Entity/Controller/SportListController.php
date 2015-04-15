<?php

/**
 * @file
 * Contains Drupal\mespronos_sports\Entity\Controller\SportListController.
 */

namespace Drupal\mespronos_sports\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;

/**
 * Provides a list controller for Sport entity.
 *
 * @ingroup mespronos_sports
 */
class SportListController extends EntityListBuilder {
  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = t('SportID');
    $header['name'] = t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\mespronos_sports\Entity\Sport */
    $row['id'] = $entity->id();
    $row['name'] = \Drupal::l(
      $this->getLabel($entity),
      new Url(
        'entity.sport.edit_form', array(
          'sport' => $entity->id(),
        )
      )
    );
    return $row + parent::buildRow($entity);
  }

}
