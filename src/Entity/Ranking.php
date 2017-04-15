<?php

namespace Drupal\mespronos\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\mespronos\RankingInterface;
use Drupal\mespronos\MPNEntityInterface;
use Drupal\mespronos_group\Entity\Group;

abstract class Ranking extends MPNContentEntityBase implements MPNEntityInterface, RankingInterface {

  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += array(
      'user_id' => \Drupal::currentUser()->id(),
    );
  }

  abstract public function getBaseTable();

  abstract public function getEntityRelated();

  abstract public function getStorageName();

  abstract public function getPosition();

  public function determinePosition($results) {
    $position = 0;
    $next_position = 0;
    $old_points = null;
    foreach ($results as $result) {
      $next_position++;
      if ($old_points != $result->points) {
        $position = $next_position;
        $old_points = $result->points;
      }
      if ($this->id() == $result->id) {
        return $position;
      }
    }
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

  public static function getRanking($entity = null,$entity_name=null,$storage_name,Group $group = null) {
    $storage = \Drupal::entityManager()->getStorage($storage_name);
    $query = \Drupal::entityQuery($storage_name);
    if(!is_null($entity_name) && !is_null($entity)) {
      $query->condition($entity_name, $entity->id());
    }
    if(!is_null($group) ) {
      $member_ids = $group->getMembers();
      $query->condition('better',$member_ids,'IN');
    }
    $query->sort('points','DESC');
    $ids = $query->execute();

    $rankings = $storage->loadMultiple($ids);
    return $rankings;
  }

  /**
   * @param \Drupal\user\Entity\User $better
   * @return \Drupal\mespronos\Entity\RankingDay
   */
  public static function getRankingForBetter(\Drupal\user\Entity\User $better, $entity = null, $entity_name = null, $storage_name) {
    $storage = \Drupal::entityManager()->getStorage($storage_name);
    $query = \Drupal::entityQuery($storage_name);
    $query->condition('better', $better->id());
    if (!is_null($entity_name) && !is_null($entity)) {
      $query->condition($entity_name, $entity->id());
    }
    $ids = $query->execute();
    if (count($ids) > 0) {
      $id = array_pop($ids);
      $rankings = $storage->load($id);
    } else {
      $rankings = [];
    }
    return $rankings;
  }

  /**
   * @param \Drupal\mespronos\Entity\MPNContentEntityBase $entity
   * @param String $entity_name
   * @param String $storage_name
   * @return integer
   */
  public static function getNumberOfBetters($entity = null, $entity_name = null, $storage_name) {
    $query = \Drupal::entityQuery($storage_name);
    if (!is_null($entity_name) && !is_null($entity)) {
      $query->condition($entity_name, $entity->id());
    }
    $ids = $query->execute();
    return count($ids);
  }

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['better'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Better'))
      ->setDescription(t('The user ID of the Bet entity author.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDefaultValueCallback('Drupal\node\Entity\Node::getCurrentUserId')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => array(
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ),
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['games_betted'] = BaseFieldDefinition::create('integer')
      ->setLabel('Games betted')
      ->setRevisionable(TRUE)
      ->setSetting('unsigned', TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'integer',
        'weight' => 6,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'number',
        'weight' => 6,
      ));

    $fields['points'] = BaseFieldDefinition::create('integer')
      ->setLabel('Points won')
      ->setRevisionable(TRUE)
      ->setSetting('unsigned', TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'integer',
        'weight' => 6,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'number',
        'weight' => 6,
      ));

    return $fields;
  }

}