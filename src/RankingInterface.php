<?php

namespace Drupal\mespronos;

interface RankingInterface {
  public static function getRankingForBetter(\Drupal\user\Entity\User $better,$type);
}