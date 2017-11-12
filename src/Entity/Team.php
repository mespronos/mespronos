<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\Team.
 */

namespace Drupal\mespronos\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\file\Entity\File;
use Drupal\mespronos\Entity\Base\MPNContentEntityBase;
use Drupal\mespronos\Entity\Interfaces\MPNEntityInterface;

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
 *     "views_data" = "Drupal\mespronos\Entity\ViewsData\TeamViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\mespronos\Entity\Form\TeamForm",
 *       "add" = "Drupal\mespronos\Entity\Form\TeamForm",
 *       "edit" = "Drupal\mespronos\Entity\Form\TeamForm",
 *       "delete" = "Drupal\mespronos\Entity\Form\MPNEntityDeleteForm",
 *     },
 *     "access" = "Drupal\mespronos\ControlHandler\TeamAccessControlHandler",
 *   },
 *   base_table = "mespronos__team",
 *   admin_permission = "administer Team entity",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "edit-form" = "/entity.team.edit_form",
 *     "delete-form" = "/entity.team.delete_form",
 *     "collection" = "/entity.team.collection"
 *   },
 *   field_ui_base_route = "team.settings"
 * )
 */
class Team extends MPNContentEntityBase implements MPNEntityInterface {
  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += array(
      'creator' => \Drupal::currentUser()->id(),
    );
  }

  public static function create(array $values = array()) {
    if (!isset($values['name']) || empty(trim($values['name']))) {
      throw new \Exception(t('The team\'s name should be set and should not be empty'));
    }
    return parent::create($values);
  }


  public function label($as_entity = false, $view_mode = 'full') {
    if ($as_entity) {
      $entity = entity_view($this, $view_mode);
      return render($entity);
    } else {
      return $this->get('name')->value;
    }
  }

  public function getLogo($style_name = 'thumbnail') {
    $logo = $this->get("field_team_logo")->first();
    if ($logo && !is_null($logo) && $logo_file = File::load($logo->getValue()['target_id'])) {
      return self::getImageAsRenderableArray($logo_file, $style_name);
    }
    else {
      return [];
    }
  }

  public function getLogoAsFile() {
    $logo = $this->get("field_team_logo")->first();
    if ($logo && !is_null($logo) && $logo_file = File::load($logo->getValue()['target_id'])) {
      return $logo_file;
    }
    return NULL;
  }

  /**
   * @param int $nb number of games to return
   * @return \Drupal\mespronos\Entity\Game[]
   */
  public function getLastGames($nb = 5) {
    $game_storage = \Drupal::entityTypeManager()->getStorage('game');
    $query = \Drupal::entityQuery('game');

    $query->condition('score_team_1', null, 'is not');
    $query->condition('score_team_2', null, 'is not');

    $group = $query->orConditionGroup()
      ->condition('team_1', $this->id())
      ->condition('team_2', $this->id());

    $query->sort('game_date', 'DESC');
    $query->range(0, $nb);

    $ids = $query->condition($group)->execute();

    $games = $game_storage->loadMultiple($ids);

    return $games;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

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

    return $fields;
  }

}
