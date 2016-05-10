<?php

namespace Drupal\mespronos_group\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\mespronos_group\GroupInterface;
use Drupal\user\UserInterface;
use Drupal\user\Entity\User;

/**
 * Defines the Group entity.
 *
 * @ingroup mespronos_group
 *
 * @ContentEntityType(
 *   id = "group",
 *   label = @Translation("Group"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\mespronos_group\GroupListBuilder",
 *     "views_data" = "Drupal\mespronos_group\Entity\GroupViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\mespronos_group\Form\GroupForm",
 *       "add" = "Drupal\mespronos_group\Form\GroupForm",
 *       "edit" = "Drupal\mespronos_group\Form\GroupForm",
 *       "delete" = "Drupal\mespronos_group\Form\GroupDeleteForm",
 *     },
 *     "access" = "Drupal\mespronos_group\GroupAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\mespronos_group\GroupHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "mespronos__group",
 *   admin_permission = "administer group entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/mespronos/group/{group}",
 *     "add-form" = "/mespronos/group/add",
 *     "edit-form" = "/admin/mespronos/group/{group}/edit",
 *     "delete-form" = "/admin/mespronos/group/{group}/delete",
 *     "collection" = "/admin/mespronos/group",
 *   },
 *   field_ui_base_route = "group.settings"
 * )
 */
class Group extends ContentEntityBase implements GroupInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += array(
      'user_id' => \Drupal::currentUser()->id(),
    );
  }

  public function label() {
    return $this->getTheName();
  }

  public static function loadByCode($code) {
    $storage = \Drupal::entityManager()->getStorage('group');
    $query = \Drupal::entityQuery('group');
    $query->condition('code', $code);
    $id = $query->execute();
    if(count($id)>0) {
      $id = array_pop($id);
      $group = $storage->load($id);
      return $group;
    }
    else {
      return false;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getTheName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCode() {
    return $this->get('code')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCode($code) {
    $this->set('code', $code);
    return $this;
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
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
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
  public function isPublished() {
    $status = (bool) $this->getEntityKey('status');
    return $status;
  }

  public function isPublishedAsVisual() {
    $status = (bool) $this->getEntityKey('status');
    return $status ? '<span class="status status-✔">✔</span>' : '<span class="status status-✖">✖</span>';

  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? NODE_PUBLISHED : NODE_NOT_PUBLISHED);
    return $this;
  }

  public function isMemberOf(User $user) {
    if($user->get('field_group')->first()) {
      $user_group = $user->get('field_group')->first()->getValue();
      return isset($user_group['target_id']) && $user_group['target_id'] == $this->id();
    }
    return false;
  }

  public function getMemberNumber() {
    $query =  \Drupal::entityQuery('user')
    ->condition('field_group',$this->id());

    $ids = $query->execute();
    return count($ids);
  }

  public function getMembers($asEntity = false) {
    $query =  \Drupal::entityQuery('user')
    ->condition('field_group',$this->id());
    $ids = $query->execute();
    if($asEntity) {
      $users = [];
      foreach ($ids as $id) {
        $user = User::load($id);
        $users[] = $user;
      }
      return $users;
    }
    return $ids;
  }

  /**
   * @param \Drupal\user\Entity\User|NULL $user
   * @return bool|Group
   */
  public static function getUserGroup(User $user = null) {
    if($user == null) {
      $user = \Drupal::currentUser();
      $user = User::load($user->id());
    }
    if($user->get('field_group')->first()) {
      $user_group = $user->get('field_group')->first()->getValue();
      $user_group = Group::load($user_group['target_id']);
      return $user_group;
    }
    return false;
  }
  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = [];
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Group entity.'))
      ->setReadOnly(TRUE);
    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Group entity.'))
      ->setReadOnly(TRUE);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Creator'))
      ->setDescription(t('The user ID of author of the Group entity.'))
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
      ->setLabel(t('Group name'))
      ->setDescription(t('The public name of the group'))
      ->setRequired(true)
      ->setSettings(array(
        'max_length' => 50,
        'text_processing' => 0,
      ))
      ->setDefaultValue('')
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

    $fields['code'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Access code'))
      ->setDescription(t('The code required to join this group'))
      ->setRequired(true)
      ->setSettings(array(
        'max_length' => 50,
        'text_processing' => 0,
      ))
      ->setDefaultValue('')
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => -3,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['hidden'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Groupe caché'))
      ->setDescription(t('Le groupe sera invisible sur les listes et ne pourra être joint qu\'en ayant son adresse'))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', array(
        'type' => 'boolean_checkbox',
        'settings' => array(
          'display_label' => TRUE,
        )
      ))
      ->setDisplayOptions('view', array('type' => 'hidden'));

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Group is published.'))
      ->setDefaultValue(TRUE);

    $fields['langcode'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Language code'))
      ->setDescription(t('The language code for the Group entity.'))
      ->setDisplayOptions('form', array(
        'type' => 'language_select',
        'weight' => 10,
      ))
      ->setDisplayConfigurable('form', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
