<?php

namespace Drupal\mespronos;
use Drupal\domain\DomainNegotiatorInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\domain\Entity\Domain;
use Drupal\mespronos\Entity\Game;
use Drupal\mespronos_group\Entity\Group;
use Drupal\user\Entity\User;

class GameManager {

  /**
   * @param $nb_hours
   * @param $limit
   * @param bool $onlyWithoutScore
   *
   * @return \Drupal\mespronos\Entity\Game[]
   * @throws \Exception
   */
  public function getUpcommingGames($nb_hours, $limit = NULL, $onlyWithoutScore = TRUE) {
    $date_to = new \DateTime(null, new \DateTimeZone('UTC'));
    $date_to->add(new \DateInterval('PT' . (int) $nb_hours . 'H'));
    $now = new \DateTime(null, new \DateTimeZone('UTC'));

    $query = \Drupal::entityQuery('game');

    $query->condition('game_date', $now->format('Y-m-d\TH:i:s'), '>');
    $query->condition('game_date', $date_to->format('Y-m-d\TH:i:s'), '<=');

    if($onlyWithoutScore) {
      $group = $query->orConditionGroup();
      $group->condition('score_team_1', NULL, 'is');
      $group->condition('score_team_2', NULL, 'is');
      $query->condition($group);
    }

    $query->sort('game_date', 'ASC');
    $query->sort('id', 'ASC');

    if($limit) {
      $query->range(0, $limit);
    }

    $ids = $query->execute();
    if ($ids) {
      return Game::loadMultiple($ids);
    }
    return [];
  }

}
