<?php

/**
 * @file
 * Contains Drupal\mespronos\BetInterface.
 */

namespace Drupal\mespronos;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a Bet entity.
 *
 * @ingroup mespronos
 */
interface BetInterface extends ContentEntityInterface, EntityOwnerInterface {
  // Add get/set methods for your configuration properties here.

}
