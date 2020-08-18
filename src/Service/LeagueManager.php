<?php
namespace Drupal\mespronos\Service;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\mespronos\Entity\League;

class LeagueManager {

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  public function __construct(Connection $connection, EntityTypeManagerInterface $entityTypeManager) {
    $this->connection = $connection;
    $this->entityTypeManager = $entityTypeManager;
  }

  public function getDaysNumber(League $league) {
    $query = \Drupal::entityQuery('day');
    $query->condition('league', $league->id());
    return $query->count()->execute();
  }

  /**
   * @return League[]
   */
  public function getActiveLeagues() {
    $leagueStorage = $this->entityTypeManager->getStorage('game');
    $query = \Drupal::entityQuery('league');
    $query->condition('status', 'active');
    $query->sort('id');
    $ids = $query->execute();
    return $leagueStorage->loadMultiple($ids);
  }

  public function getBettersNumber(League $league) {
    $query = $this->connection->select('mespronos__ranking_league', 'rl');
    $query->addExpression('count(rl.better)', 'nb_better');
    $query->condition('rl.league', $league->id());
    $results = $query->execute()->fetchObject();
    return $results->nb_better;
  }

}
