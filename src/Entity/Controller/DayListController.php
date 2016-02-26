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
    $header = [];
    $header['id'] = t('DayID');
    $header['league'] = t('League');
    $header['name'] = t('Day name');
    $header['nb_game'] = t('Game items');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\mespronos\Entity\Day */
    $league = $entity->getLeague();
    $row = [];
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

  protected function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);
    $user = \Drupal::currentUser();

    if ($user->hasPermission('set marks')) {
      $operations['recount_points'] = array(
        'title' => $this->t('Re-count points'),
        'weight' => 20,
        'url' =>
          new Url(
            'entity.day.recount_points', array(
            'day' => $entity->id(),
          ))
      );
      $operations['recount_ranking'] = array(
        'title' => $this->t('Re-count ranking'),
        'weight' => 20,
        'url' =>
          new Url(
            'entity.day.recount_ranking', array(
            'day' => $entity->id(),
          ))
      );
    }

    return $operations;
  }
}
