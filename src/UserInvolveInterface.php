<?php

/**
 * @file
 * Contains Drupal\mespronos\UserInvolveInterface.
 */

namespace Drupal\mespronos;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a UserInvolve entity.
 *
 * @ingroup mespronos
 */
interface UserInvolveInterface extends ContentEntityInterface, EntityOwnerInterface {
  // Add get/set methods for your configuration properties here.

}
