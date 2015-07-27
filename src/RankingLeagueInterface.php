<?php

/**
 * @file
 * Contains Drupal\mespronos\RankingLeagueInterface.
 */

namespace Drupal\mespronos;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a RankingLeague entity.
 *
 * @ingroup mespronos
 */
interface RankingLeagueInterface extends ContentEntityInterface, EntityOwnerInterface {
  // Add get/set methods for your configuration properties here.

}
