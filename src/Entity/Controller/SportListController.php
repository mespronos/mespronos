<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\Controller\SportListController.
 */

namespace Drupal\mespronos\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;

/**
 * Provides a list controller for Sport entity.
 *
 * @ingroup mespronos
 */
class SportListController extends EntityListBuilder {
  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = t('ID');
    $header['name'] = t('Label');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\mespronos\Entity\Sport */
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
