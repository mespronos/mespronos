<?php

/**
 * @file
 * Contains Drupal\mespronos_leagues\LeagueInterface.
 */

namespace Drupal\mespronos_leagues;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a League entity.
 * @ingroup account
 */
interface LeagueInterface extends ContentEntityInterface, EntityOwnerInterface
{

  // Add get/set methods for your configuration properties here.
}
