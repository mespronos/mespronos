<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\Controller\UserInvolveListController.
 */

namespace Drupal\mespronos\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;

/**
 * Provides a list controller for UserInvolve entity.
 *
 * @ingroup mespronos
 */
class UserInvolveListController extends EntityListBuilder {
  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('UserInvolveID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\mespronos\Entity\UserInvolve */
    $row['id'] = $entity->id();
    $row['name'] = \Drupal::l(
      $this->getLabel($entity),
      new Url(
        'entity.user_involve.edit_form', array(
          'user_involve' => $entity->id(),
        )
      )
    );
    return $row + parent::buildRow($entity);
  }

}
