<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\Day.
 */

namespace Drupal\mespronos\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\mespronos\MPNEntityInterface;

/**
 * Defines the Day entity.
 *
 * @ingroup mespronos
 *
 * @ContentEntityType(
 *   id = "day",
 *   label = @Translation("Day entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\mespronos\Entity\Controller\DayListController",
 *     "views_data" = "Drupal\mespronos\Entity\ViewsData\DayViewsData",
 *
 *     "form" = {
 *       "add" = "Drupal\mespronos\Entity\Form\DayForm",
 *       "edit" = "Drupal\mespronos\Entity\Form\DayForm",
 *       "delete" = "Drupal\mespronos\Entity\Form\MPNDeleteForm",
 *     },
 *     "access" = "Drupal\mespronos\ControlHandler\DayAccessControlHandler",
 *   },
 *   base_table = "mespronos__day",
 *   admin_permission = "administer Day entity",
 *   fieldable = FALSE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/entity.day.canonical",
 *     "edit-form" = "/entity.day.edit_form",
 *     "recalculate_ranking" = "/entity.day.recalculate_ranking",
 *     "delete-form" = "/entity.day.delete_form"
 *   }
 * )
 */
class Day extends MPNContentEntityBase implements MPNEntityInterface
{

  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += array(
      'user_id' => \Drupal::currentUser()->id(),
    );
  }

  /**
   * @return \Drupal\mespronos\Entity\League
   */
  public function getLeague() {
    $league = League::load($this->get('league')->target_id);
    return $league;
  }

  public function getNbGame() {
    $query = \Drupal::entityQuery('game')->condition('day', $this->id());
    $ids = $query->execute();
    return count($ids);
  }

  public function getNbGameWIthScore() {
    $query = \Drupal::entityQuery('game')
      ->condition('day', $this->id())
      ->condition('score_team_1',NULL,'IS NOT')
      ->condition('score_team_2',NULL,'IS NOT');
    $ids = $query->execute();
    return count($ids);
  }

  public function label() {
    return $this->get('name')->value;
  }
  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['creator'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of the Day entity author.'))
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

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Day entity.'))
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

    $fields['day_date'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('Date'))
      ->setDescription(t('The day\'s date'))
      ->setSettings(array(
        'default_value' => '',
        'max_length' => 50,
        'text_processing' => 0,
      ))
      ->setDefaultValue(array(0 => array(
        'default_date_type' => 'now',
        'default_date' => 'now',
      )))
      ->setDisplayOptions('view', array(
        'type' => 'datetime_default',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'datetime_default',
        'weight' => 2,
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
    $fields['number'] = BaseFieldDefinition::create('integer')
      ->setLabel('Day number')
      ->setRevisionable(TRUE)
      ->setSetting('unsigned', TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'integer',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'number',
      ));

    return $fields;
  }
}
