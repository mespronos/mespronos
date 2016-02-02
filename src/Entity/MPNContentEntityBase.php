<?php

namespace Drupal\mespronos\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\user\UserInterface;

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


}