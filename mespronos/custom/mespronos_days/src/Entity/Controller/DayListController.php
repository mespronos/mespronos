<?php

/**
 * @file
 * Contains Drupal\mespronos_days\Entity\Controller\DayListController.
 */

namespace Drupal\mespronos_days\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;

/**
 * Provides a list controller for Day entity.
 *
 * @ingroup mespronos_days
 */
class DayListController extends EntityListBuilder
{

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = t('DayID');
    $header['name'] = t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\mespronos_days\Entity\Day */
    $row['id'] = $entity->id();
    $row['name'] = \Drupal::l(
        $this->getLabel($entity),
        new Url(
          'day.edit', array(
            'day' => $entity->id(),
        )
      )
    );
    return $row + parent::buildRow($entity);
  }
}
