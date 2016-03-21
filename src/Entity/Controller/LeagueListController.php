<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\Controller\LeagueListController.
 */

namespace Drupal\mespronos\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;

/**
 * Provides a list controller for League entity.
 *
 * @ingroup mespronos
 */
class LeagueListController extends EntityListBuilder {
  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = [];
    $header['id'] = t('ID');
    $header['sport'] = t('Sport');
    $header['name'] = t('Name');
    $header['status'] = t('Status');
    $header['betting_type'] = t('Betting type');
    $header['classement'] = t('Classement activé ?');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\mespronos\Entity\League */
    $row = [];
    $sport = $entity->getSport();
    $row['id'] = $entity->id();
    $row['sport'] = $sport->label();
    $row['name'] = \Drupal::l(
      $this->getLabel($entity),
      new Url(
        'entity.league.edit_form', array(
          'league' => $entity->id(),
        )
      )
    );
    $row['status'] = $entity->getStatus(false);
    $row['betting_type'] = $entity->getBettingType(false);
    $row['classement'] = $entity->hasClassement() ? '✓' : '✗';
    return $row + parent::buildRow($entity);
  }

  protected function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);
    $user = \Drupal::currentUser();

    if ($user->hasPermission('set marks')) {
      $operations['recount_points'] = array(
        'title' => $this->t('Re-count points and ranking'),
        'weight' => 20,
        'url' =>
          new Url(
            'entity.league.recount_points', array(
            'league' => $entity->id(),
          ))
      );
      $operations['archive'] = array(
        'title' => $this->t('Archive'),
        'weight' => 30,
        'url' => new Url('entity.league.archive',['league' => $entity->id()])
      );
    }

    return $operations;
  }

}
