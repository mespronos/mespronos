<?php

namespace Drupal\mespronos\Entity\Interfaces;

interface RankingInterface {
  public static function getRankingForBetter(\Drupal\user\Entity\User $better, $entity, $entity_name, $storage_name);
}