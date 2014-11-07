<?php

/**
 * @file
 * Contains Drupal\mespronos_sports\SportInterface.
 */

namespace Drupal\mespronos_sports;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a Sport entity.
 * @ingroup account
 */
interface SportInterface extends ContentEntityInterface, EntityOwnerInterface
{

  // Add get/set methods for your configuration properties here.
}
