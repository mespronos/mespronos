<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\Team.
 */

namespace Drupal\mespronos\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\mespronos\TeamInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Team entity.
 *
 * @ingroup mespronos
 *
 * @ContentEntityType(
 *   id = "team",
 *   label = @Translation("Team"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\mespronos\Entity\Controller\TeamListController",
 *     "views_data" = "Drupal\mespronos\Entity\TeamViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\mespronos\Entity\Form\TeamForm",
 *       "add" = "Drupal\mespronos\Entity\Form\TeamForm",
 *       "edit" = "Drupal\mespronos\Entity\Form\TeamForm",
 *       "delete" = "Drupal\mespronos\Entity\Form\TeamDeleteForm",
 *     },
 *     "access" = "Drupal\mespronos\TeamAccessControlHandler",
 *   },
 *   base_table = "mespronos__team",
 *   admin_permission = "administer Team entity",
 *   fieldable = FALSE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/entity.team.canonical",
 *     "edit-form" = "/entity.team.edit_form",
 *     "delete-form" = "/entity.team.delete_form",
 *     "collection" = "/entity.team.collection"
 *   },
 *   field_ui_base_route = "team.settings"
 * )
 */
class Team extends ContentEntityBase implements TeamInterface {
    
  use EntityChangedTrait;
  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += array(
      'creator' => \Drupal::currentUser()->id(),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getChangedTime() {
    return $this->get('updated')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('creator')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('creator')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('creator', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('creator', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Team entity.'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Team entity.'))
      ->setReadOnly(TRUE);

    $fields['creator'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of the Team entity author.'))
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

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Team entity.'))
      ->setSettings(array(
        'default_value' => '',
        'max_length' => 50,
        'text_processing' => 0,
      ))
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => -4,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['updated'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
