<?php
namespace Drupal\mespronos\Service;

use Drupal\user\Entity\User;

class StatisticsManager {

  public function getStatistics() : array {
    $stats = [];
    $stats['members'] = t('@nb members', ['@nb' => $this->getMembersNumber()]);
    $stats['leagues'] = t('@nb leagues', ['@nb' => $this->getLeaguesNumber()]);
    $stats['games'] = t('@nb games', ['@nb' => $this->getGamesNumber()]);
    $stats['bets'] = t('@nb bets', ['@nb' => $this->getBetsNumber()]);

    if (\Drupal::moduleHandler()->moduleExists('mespronos_group')) {
      $stats['groups'] = t('@nb groups', ['@nb' => $this->getGroupsNumber()]);
    }
    return $stats;
  }

  public function getUserStatistics(User $user) : array {
    $stats = [];
    $stats['nb_bets'] = $this->getBetsNumber($user);
    return $stats;
  }

  private function getMembersNumber() {
    $query = \Drupal::entityQuery('user');
    $ids = $query->execute();
    return count($ids);
  }

  private function getGamesNumber() {
    $query = \Drupal::entityQuery('game');
    $ids = $query->execute();
    return count($ids);
  }

  private function getLeaguesNumber() {
    $query = \Drupal::entityQuery('league');
    $ids = $query->execute();
    return count($ids);
  }

  private function getGroupsNumber() {
    $query = \Drupal::entityQuery('group');
    $ids = $query->execute();
    return count($ids);
  }

  private function getBetsNumber(User $user = null) {
    $query = \Drupal::entityQuery('bet');
    if ($user) {
      $query->condition('better', $user->id());
    }
    $ids = $query->execute();
    return count($ids);
  }

  public function getNextGamesStats($nb_games) {
    $_games = [];
    /** @var \Drupal\mespronos\Entity\Game[] $games */
    $games = \Drupal::service('mespronos.game_manager')->getUpcommingGames(480, 10);
    foreach ($games as $game) {
      $game_date = \DateTime::createFromFormat('Y-m-d\TH:i:s', $game->getGameDate(), new \DateTimeZone("GMT"));
      $game_date->setTimezone(new \DateTimeZone("Europe/Paris"));
      $_games[] = [
        'id' => $game->id(),
        'team_1' => $game->getTeam1()->label(),
        'team_2' => $game->getTeam2()->label(),
        'date' => $game_date->format('d/m/Y H:i:s'),
        'nb_bet' => $game->getNbBets(),
      ];
    }

    return $_games;
  }

}
