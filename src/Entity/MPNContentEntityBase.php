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
    return $this->get('user_id')->entity;
  }

  /**
   * @return integer
   */
  public function getOwnerId() {
    return $this->get('better')->target_id;
  }

  public function setOwner(UserInterface $account) {
    $this->set('better', $account->id());
    return $this;
  }

  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }


}