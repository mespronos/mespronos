<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\Controller\RankingLeagueListController.
 */

namespace Drupal\mespronos\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;

/**
 * Provides a list controller for RankingLeague entity.
 *
 * @ingroup mespronos
 */
class RankingLeagueListController extends EntityListBuilder {
  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('RankingLeagueID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\mespronos\Entity\RankingLeague */
    $row['id'] = $entity->id();
    $row['name'] = \Drupal::l(
      $this->getLabel($entity),
      new Url(
        'entity.ranking_league.edit_form', array(
          'ranking_league' => $entity->id(),
        )
      )
    );
    return $row + parent::buildRow($entity);
  }

}
