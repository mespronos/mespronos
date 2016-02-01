<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\Controller\RankingDayListController.
 */

namespace Drupal\mespronos\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;

/**
 * Provides a list controller for RankingDay entity.
 *
 * @ingroup mespronos
 */
class RankingDayListController extends EntityListBuilder {
  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = [];
    $header['id'] = $this->t('RankingDayID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\mespronos\Entity\RankingDay */
    $row = [];
    $row['id'] = $entity->id();
    $row['name'] = \Drupal::l(
      $this->getLabel($entity),
      new Url(
        'entity.ranking_day.edit_form', array(
          'ranking_day' => $entity->id(),
        )
      )
    );
    return $row + parent::buildRow($entity);
  }

}
