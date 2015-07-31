<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\UserInvolve.
 */

namespace Drupal\mespronos\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\mespronos\UserInvolveInterface;
use Drupal\user\UserInterface;

/**
 * Defines the UserInvolve entity.
 *
 * @ingroup mespronos
 *
 * @ContentEntityType(
 *   id = "user_involve",
 *   label = @Translation("UserInvolve entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\mespronos\Entity\Controller\UserInvolveListController",
 *     "views_data" = "Drupal\mespronos\Entity\UserInvolveViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\mespronos\Entity\Form\UserInvolveForm",
 *       "add" = "Drupal\mespronos\Entity\Form\UserInvolveForm",
 *       "edit" = "Drupal\mespronos\Entity\Form\UserInvolveForm",
 *       "delete" = "Drupal\mespronos\Entity\Form\UserInvolveDeleteForm",
 *     },
 *     "access" = "Drupal\mespronos\UserInvolveAccessControlHandler",
 *   },
 *   base_table = "mespronos__user_involve",
 *   admin_permission = "administer UserInvolve entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/user_involve/{user_involve}",
 *     "edit-form" = "/admin/user_involve/{user_involve}/edit",
 *     "delete-form" = "/admin/user_involve/{user_involve}/delete"
 *   },
 *   field_ui_base_route = "user_involve.settings"
 * )
 */
class UserInvolve extends ContentEntityBase implements UserInvolveInterface {
  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += array(
      'user_id' => \Drupal::currentUser()->id(),
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
    return $this->get('changed')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the UserInvolve entity.'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the UserInvolve entity.'))
      ->setReadOnly(TRUE);

    $fields['user'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User'))
      ->setDescription(t('User entity reference'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'entity_reference',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'options_select',
        'settings' => array(),
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['league'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('League'))
      ->setDescription(t('League entity reference'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'league')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'entity_reference',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'options_select',
        'settings' => array(),
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
