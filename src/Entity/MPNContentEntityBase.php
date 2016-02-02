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
    if(self::hasField('better')) {
      return $this->get('better')->entity;
    }
    else {
      return $this->get('user_id')->entity;
    }
  }

  /**
   * @return integer
   */
  public function getOwnerId() {
    if(self::hasField('better')) {
      return $this->get('better')->target_id;
    }
    else {
      return $this->get('user_id')->target_id;
    }
  }

  public function setOwner(UserInterface $account) {
    if(self::hasField('better')) {
      $this->set('better', $account->id());
    }
    else {
      $this->set('user_id', $account->id());
    }
    return $this;
  }

  public function setOwnerId($uid) {
    if(self::hasField('better')) {
      $this->set('better', $uid);
    }
    else {
      $this->set('user_id', $uid);
    }
    return $this;
  }


}