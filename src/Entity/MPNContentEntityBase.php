<?php

namespace Drupal\mespronos\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\user\UserInterface;
use Drupal\file\Entity\File;

abstract class MPNContentEntityBase extends ContentEntityBase {

  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  public function getChangedTime() {
    return $this->get('changed')->value;
  }

  /**
   * @return UserInterface
   */
  public function getOwner() {
    if(static::hasField('better')) {
      return $this->get('better')->entity;
    }
    else {
      return $this->get('creator')->entity;
    }
  }

  /**
   * @return integer
   */
  public function getOwnerId() {
    if(static::hasField('better')) {
      return $this->get('better')->target_id;
    }
    else {
      return $this->get('creator')->target_id;
    }
  }

  public function setOwner(UserInterface $account) {
    if(static::hasField('better')) {
      $this->set('better', $account->id());
    }
    else {
      $this->set('creator', $account->id());
    }
    return $this;
  }

  public function setOwnerId($uid) {
    if(static::hasField('better')) {
      $this->set('better', $uid);
    }
    else {
      $this->set('creator', $uid);
    }
    return $this;
  }

  public static function getImageAsRenderableArray($image_file,$style_name= 'thumbnail') {
    $render_array = [
      '#theme' => 'image_style',
      '#width' => null,
      '#height' => null,
      '#style_name' => $style_name,
      '#uri' => $image_file->getFileUri(),
    ];
    return $render_array;
  }

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = [];
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the entity.'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the entity.'))
      ->setReadOnly(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }
}