<?php
namespace Drupal\mespronos;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a entity.
 *
 * @ingroup mespronos
 */
interface EntityInterface extends ContentEntityInterface, EntityOwnerInterface {
  // Add get/set methods for your configuration properties here.

}
