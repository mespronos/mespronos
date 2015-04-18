<?php

/**
 * @file
 * Contains Drupal\mespronos_teams\TeamInterface.
 */

namespace Drupal\mespronos_teams;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a Team entity.
 *
 * @ingroup mespronos_teams
 */
interface TeamInterface extends ContentEntityInterface, EntityOwnerInterface {
  // Add get/set methods for your configuration properties here.

}
