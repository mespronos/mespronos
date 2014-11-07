<?php

/**
 * @file
 * Contains Drupal\mespronos_days\DayInterface.
 */

namespace Drupal\mespronos_days;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a Day entity.
 * @ingroup account
 */
interface DayInterface extends ContentEntityInterface, EntityOwnerInterface
{

  // Add get/set methods for your configuration properties here.
}
