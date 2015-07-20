<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\Controller\GameListController.
 */

namespace Drupal\mespronos\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;

/**
 * Provides a list controller for Game entity.
 *
 * @ingroup mespronos
 */
class GameListController extends EntityListBuilder {
  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('GameID');
    $header['league'] = $this->t('League');
    $header['day'] = $this->t('Day');
    $header['game_date'] = $this->t('Date');
    $header['name'] = $this->t('Name');
    $header['score'] = $this->t('Mark');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\mespronos\Entity\Game */
    $date = \DateTime::createFromFormat('Y-m-d\TH:i:s',$entity->get('game_date')->value);
    $league = $entity->getLeague();
    $day = $entity->getDay();
    $row['id'] = $entity->id();
    $row['league'] = $league->label();
    $row['day'] = $day->label();
    $row['game_date'] = format_date($date->format('U'),'short');
    $row['name'] = \Drupal::l(
      $this->getLabel($entity),
      new Url(
        'entity.game.edit_form', array(
          'game' => $entity->id(),
        )
      )
    );
    $row['score'] = $entity->get('score_team_1')->value . ' - ' . $entity->get('score_team_2')->value;
    return $row + parent::buildRow($entity);
  }

}
