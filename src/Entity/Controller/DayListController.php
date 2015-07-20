<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\Controller\DayListController.
 */

namespace Drupal\mespronos\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;

/**
 * Provides a list controller for Day entity.
 *
 * @ingroup mespronos
 */
class DayListController extends EntityListBuilder
{

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = t('DayID');
    $header['league'] = t('League');
    $header['name'] = t('Day name');
    $header['nb_game'] = t('Game number');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\mespronos\Entity\Day */
    dpm($entity);
    $league = $entity->getLeague();
    $row['id'] = $entity->id();
    $row['league'] = $league->label();
    $row['name'] = \Drupal::l(
        $this->getLabel($entity),
        new Url(
          'entity.day.edit_form', array(
            'day' => $entity->id(),
        )
      )
    );
    $row['nb_game'] = $entity->getNbGame();
    return $row + parent::buildRow($entity);
  }
}
