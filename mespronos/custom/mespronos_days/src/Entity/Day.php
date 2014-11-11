<?php

/**
 * @file
 * Contains Drupal\mespronos_days\Entity\Day.
 */

namespace Drupal\mespronos_days\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\mespronos_days\DayInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Day entity.
 *
 * @ingroup mespronos_days
 *
 * @ContentEntityType(
 *   id = "day",
 *   label = @Translation("Day entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\mespronos_days\Entity\Controller\DayListController",
 *     "views_data" = "Drupal\mespronos_days\Entity\DayViewsData",
 *
 *     "form" = {
 *       "add" = "Drupal\mespronos_days\Entity\Form\DayForm",
 *       "edit" = "Drupal\mespronos_days\Entity\Form\DayForm",
 *       "delete" = "Drupal\mespronos_days\Entity\Form\DayDeleteForm",
 *     },
 *     "access" = "Drupal\mespronos_days\DayAccessControlHandler",
 *   },
 *   base_table = "day",
 *   admin_permission = "administer Day entity",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "league.view",
 *     "edit-form" = "day.edit",
 *     "admin-form" = "day.settings",
 *     "delete-form" = "day.delete"
 *   },
 *   field_ui_base_route = "day.settings"
 * )
 */
class Day extends ContentEntityBase implements DayInterface {

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
                                       ->setDescription(t('The ID of the Day entity.'))
                                       ->setReadOnly(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
                                         ->setLabel(t('UUID'))
                                         ->setDescription(t('The UUID of the Day entity.'))
                                         ->setReadOnly(TRUE);


    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
                                            ->setLabel(t('Authored by'))
                                            ->setDescription(t('The user ID of the {{ entity_class }} entity author.'))
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
                                            ->setDisplayConfigurable('form', TRUE)
                                            ->setDisplayConfigurable('view', TRUE);

    $fields['number'] = BaseFieldDefinition::create('integer')
                                           ->setLabel(t('Numéro de journée'))
                                           ->setDescription(t('Le numéro de la journée'))
                                           ->setDisplayOptions('view', array(
                                             'type' => 'hidden'
                                           ))
                                           ->setDisplayOptions('form', array(
                                             'type'   => 'integer',
                                             'weight' => -4,
                                           ))
                                            ->setRequired(true)
                                           ->setDisplayConfigurable('form', TRUE)
                                           ->setDisplayConfigurable('view', TRUE);

    $fields['league'] = BaseFieldDefinition::create('entity_reference')
                                           ->setLabel(t('Compétition'))
                                           ->setDescription(t('La compétition attachée à cette journée'))
                                           ->setSetting('target_type', 'league')
                                           ->setSetting('handler', 'default')
                                           ->setDisplayOptions('view', array('type' => 'hidden'))
                                           ->setDisplayOptions('form', array(
                                             'type'     => 'entity_reference_autocomplete',
                                             'settings' => array(
                                               'match_operator'    => 'CONTAINS',
                                               'size'              => 60,
                                               'autocomplete_type' => 'tags',
                                               'placeholder'       => '',
                                             ),
                                             'weight'   => -3,
                                           ))
                                           ->setDisplayConfigurable('form', TRUE)
                                           ->setDisplayConfigurable('view', TRUE);


    $fields['name'] = BaseFieldDefinition::create('string')
                                         ->setLabel(t('Name'))
                                         ->setDescription(t('Nom de la journée.'))
                                         ->setSettings(array(
                                           'default_value'   => '',
                                           'max_length'      => 50,
                                           'text_processing' => 0,
                                         ))
                                         ->setDisplayOptions('view', array(
                                           'label'  => 'above',
                                           'type'   => 'string',
                                           'weight' => -4,
                                         ))
                                         ->setDisplayOptions('form', array(
                                           'type'   => 'string',
                                           'weight' => -4,
                                         ))
                                         ->setDisplayConfigurable('form', TRUE)
                                         ->setDisplayConfigurable('view', TRUE);

    $fields['langcode'] = BaseFieldDefinition::create('language')
                                             ->setLabel(t('Language code'))
                                             ->setDescription(t('The language code of Day entity.'));

    $fields['created'] = BaseFieldDefinition::create('created')
                                            ->setLabel(t('Created'))
                                            ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
                                            ->setLabel(t('Changed'))
                                            ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }
}
