<?php

namespace Drupal\mespronos\Entity\Interfaces;

use \Drupal\user\Entity\User;

interface RankingInterface {

  public static function getRankingForBetter(User $better, $entity, $entity_name, $storage_name);

}