<?php

namespace Drupal\mespronos;

interface RankingInterface {
  public static function getRankingForBetter(\Drupal\Core\Session\AccountProxyInterface $better,$type);
}