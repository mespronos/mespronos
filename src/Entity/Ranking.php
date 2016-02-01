<?php

namespace Drupal\mespronos\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\user\UserInterface;

abstract class Ranking extends ContentEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += array(
      'user_id' => \Drupal::currentUser()->id(),
    );
  }

  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  public function getChangedTime() {
    return $this->get('changed')->value;
  }

  public function getOwner() {
    return $this->get('better')->entity;
  }

  public function getOwnerId() {
    return $this->get('better')->target_id;
  }

  public function setOwnerId($uid) {
    $this->set('better', $uid);
    return $this;
  }

  public function setOwner(UserInterface $account) {
    $this->set('better', $account->id());
    return $this;
  }

  public function setGameBetted($nb_games_betted) {
    $this->set('games_betted', $nb_games_betted);
    return $this;
  }

  public function getGameBetted() {
    return $this->get('games_betted')->value;
  }

  public function setPoints($points) {
    $this->set('points', $points);
    return $this;
  }

  public function getPoints() {
    return $this->get('points')->value;
  }

  public function getPosition() {
    return $this->get('position')->value;
  }

}