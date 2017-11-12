<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\Controller\RankingGeneralListController.
 */

namespace Drupal\mespronos\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;

/**
 * Provides a list controller for RankingGeneral entity.
 *
 * @ingroup mespronos
 */
class RankingGeneralListController extends EntityListBuilder {
  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = [];
    $header['id'] = $this->t('RankingGeneralID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\mespronos\Entity\RankingBaseGeneral */
    $row = [];
    $row['id'] = $entity->id();
    $row['name'] = \Drupal::l(
      $this->getLabel($entity),
      new Url(
        'entity.ranking_general.edit_form', array(
          'ranking_league' => $entity->id(),
        )
      )
    );
    return $row + parent::buildRow($entity);
  }

}
