<?php

namespace Drupal\mespronos_group;

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
    $header['id'] = $this->t('Group ID');
    $header['name'] = $this->t('Name');
    $header['code'] = $this->t('Access code');
    $header['status'] = $this->t('Status');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(Group $entity) {
    /* @var $entity \Drupal\mespronos_group\Entity\Group */
    $row['id'] = $entity->id();
    $row['name'] =  $entity->label();
    $row['code'] =  $entity->getCode();
    $status = ['#markup' => $entity->isPublished(true)];
    $row['status'] =  render($status);
    return $row + parent::buildRow($entity);
  }

}
