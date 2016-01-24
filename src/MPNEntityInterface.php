<?php
namespace Drupal\mespronos;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a entity.
 *
 * @ingroup mespronos
 */
interface MPNEntityInterface extends ContentEntityInterface, EntityOwnerInterface {
  // Add get/set methods for your configuration properties here.

}
