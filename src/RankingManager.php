<?php

namespace Drupal\mespronos;

use Drupal\mespronos\Entity\RankingGeneral;

class RankingManager {

  /**
   * @param int $number
   * @param null $group
   *
   * @return \Drupal\mespronos\Entity\RankingGeneral[]
   */
  public function getTop($number = 3, $group = NULL) : array {
    $query = \Drupal::entityQuery('ranking_general');
    if($group === NULL && \Drupal::service('mespronos.domain_manager')->getGroupFromDomain()) {
      $group = \Drupal::service('mespronos.domain_manager')->getGroupFromDomain();
      $member_ids = $group->getMembers();
      if (\count($member_ids) > 0) {
        $query->condition('better', $member_ids, 'IN');
      }
      else {
        $query->condition('better', 0);
      }
    }
    elseif(\Drupal::moduleHandler()->moduleExists('mespronos_group')) {
      $queryUserToExclude = \Drupal::entityQuery('user')->condition('bet_private', 1)->execute();
      if (\count($queryUserToExclude) > 0) {
        $query->condition('better', $queryUserToExclude,'NOT IN');
      }
    }
    $query->sort('points','DESC');
    $query->range(0, $number);
    $ids = $query->execute();
    return RankingGeneral::loadMultiple($ids);
  }

}
