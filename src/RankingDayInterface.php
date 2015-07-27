<?php

/**
 * @file
 * Contains Drupal\mespronos\RankingDayInterface.
 */

namespace Drupal\mespronos;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a RankingDay entity.
 *
 * @ingroup mespronos
 */
interface RankingDayInterface extends ContentEntityInterface, EntityOwnerInterface {
  // Add get/set methods for your configuration properties here.

}
