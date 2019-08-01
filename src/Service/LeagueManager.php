<?php
namespace Drupal\mespronos\Service;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;
use Drupal\mespronos\Entity\League;

class LeagueManager {

  /** @var \Drupal\Core\Database\Connection  */
  protected $connection;

  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  public function getDaysNumber(League $league) {
    $query = \Drupal::entityQuery('day');
    $query->condition('league', $league->id());
    return $query->count()->execute();
  }

  public function getBettersNumber(League $league) {
    $query = $this->connection->select('mespronos__ranking_league', 'rl');
    $query->addExpression('count(rl.better)', 'nb_better');
    $query->condition('rl.league', $league->id());
    $results = $query->execute()->fetchObject();
    return $results->nb_better;
  }

}
