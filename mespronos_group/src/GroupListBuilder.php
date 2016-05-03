<?php

namespace Drupal\mespronos_group;

use Drupal\Core\Entity\Entity;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;
use Drupal\mespronos_group\Entity\Group;

/**
 * Defines a class to build a listing of Group entities.
 *
 * @ingroup mespronos_group
 */
class GroupListBuilder extends EntityListBuilder {

  use LinkGeneratorTrait;

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = [
      'id' => $this->t('Group ID'),
      'name' => $this->t('Name'),
      'code' => $this->t('Access code'),
      'status' => $this->t('Status'),
    ];
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row = [];
    /* @var $entity \Drupal\mespronos_group\Entity\Group */
    $row['id'] = $entity->id();
    $row['name'] = $this->l(
      $entity->label(),
      new Url(
        'entity.group.canonical', array(
          'group' => $entity->id(),
        )
      )
    );
    $row['code'] =  $entity->getCode();
    $status = ['#markup' => $entity->isPublishedAsVisual()];
    $row['status'] =  render($status);
    return $row + parent::buildRow($entity);
  }

}
