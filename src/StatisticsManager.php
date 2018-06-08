<?php

namespace Drupal\mespronos;
use Drupal\Core\Session\AccountInterface;
use Drupal\domain\DomainNegotiatorInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\domain\Entity\Domain;
use Drupal\mespronos_group\Entity\Group;
use Drupal\user\Entity\User;

/**
 * Class MespronosDomainManager.
 */
class StatisticsManager {


  public function getStatistics() {
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

  public function getUserStatistics(User $user) {
    $stats = [];
    $stats['nb_bets'] = self::getBetsNumber($user);
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
}
