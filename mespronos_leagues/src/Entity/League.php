<?php

/**
 * @file
 * Contains Drupal\mespronos_leagues\Entity\League.
 */

namespace Drupal\mespronos_leagues\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\mespronos_leagues\LeagueInterface;
use Drupal\user\UserInterface;

/**
 * Defines the League entity.
 *
 * @ingroup mespronos_leagues
 *
 * @ContentEntityType(
 *   id = "league",
 *   label = @Translation("League entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\mespronos_leagues\Entity\Controller\LeagueListController",
 *     "views_data" = "Drupal\mespronos_leagues\Entity\LeagueViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\mespronos_leagues\Entity\Form\LeagueForm",
 *       "add" = "Drupal\mespronos_leagues\Entity\Form\LeagueForm",
 *       "edit" = "Drupal\mespronos_leagues\Entity\Form\LeagueForm",
 *       "delete" = "Drupal\mespronos_leagues\Entity\Form\LeagueDeleteForm",
 *     },
 *     "access" = "Drupal\mespronos_leagues\LeagueAccessControlHandler",
 *   },
 *   base_table = "league",
 *   admin_permission = "administer League entity",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "entity.league.canonical",
 *     "edit-form" = "entity.league.edit_form",
 *     "delete-form" = "entity.league.delete_form",
 *     "collection" = "entity.league.collection"
 *   },
 *   field_ui_base_route = "league.settings"
 * )
 */
class League extends ContentEntityBase implements LeagueInterface {
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
      ->setDescription(t('The ID of the League entity.'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the League entity.'))
      ->setReadOnly(TRUE);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of the League entity author.'))
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
      ->setDescription(t('The name of the League entity.'))
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
    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Nom'))
      ->setDescription(t('Nom de la compétition'))
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
        'type' => 'string',
        'weight' => -4,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    //Création d'un champ booléen avec un widget checkbox
    $fields['classement'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Classement activé'))
      ->setDescription(t('Doit-on calculer le classement entre les équipes pour cette competitions'))
      //est-ce que l'on autorise les modifications d'affichage dans le formulaire
      ->setDisplayConfigurable('form', TRUE)
      //est-ce que l'on autorise les modifications d'affichage en frontoffice
      ->setDisplayConfigurable('view', TRUE)
      //définition de la valeur par défaut
      ->setDefaultValue(TRUE)
      //définition des options d'affichage par défaut (front => view, back => form)
      ->setDisplayOptions('form', array(
        //on veut une checkbox
        'type' => 'boolean_checkbox',
        'settings' => array(
          'display_label' => TRUE,
        )
      ))
      ->setDisplayOptions('view', array(
        //pas d'affichage en front
        'type' => 'hidden',
      ));
    //Création d'une propriété "liste de texte"
    $fields['status'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Statut du championnat'))
      ->setRequired(true)
      ->setSettings(array(
        //définition des valeurs possible
        //@todo : à externaliser dans une méthode
        'allowed_values' => array(
          'active' => 'En cours',
          'over' => 'Terminé',
          'archived' => 'Archivé',
        ),
      ))
      //définition de la valeur par défaut
      ->setDefaultValue('active')
      ->setDisplayOptions('view', array(
        'type' => 'hidden',
      ))
      ->setDisplayOptions('form', array(
        'type' => 'options_select',
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['langcode'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Language code'))
      ->setDescription(t('The language code of League entity.'));

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
