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
 *       "delete" = "Drupal\mespronos\Entity\Form\MPNEntityDeleteForm",
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
 *     "canonical" = "/mespronos/day/{day}",
 *     "edit-form" = "/entity.day.edit_form",
 *     "recount_points" = "/entity.day.recount_points",
 *     "recount_ranking" = "/entity.day.recount_ranking",
 *     "delete-form" = "/entity.day.delete_form",
 *     "collection" = "/entity.sport.collection"
 *   },
 *   field_ui_base_route = "day.settings"
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

  /**
   * @return integer
   */
  public function getLeagueID() {
    $league = $this->get('league')->target_id;
    return $league;
  }

  /**
   * Return the number of games of the day
   *
   * @return int
   *   Number of games for the day
   */
  public function getNbGame() {
    $query = \Drupal::entityQuery('game')->condition('day', $this->id());
    $ids = $query->execute();
    return count($ids);
  }

  /**
   * Return all games for day
   *
   * @return \Drupal\mespronos\Entity\Game[]
   */
  public function getGames() {
    $game_storage = \Drupal::entityManager()->getStorage('game');
    $ids = $this->getGamesId();
    $games = $game_storage->loadMultiple($ids);

    return $games;
  }

  /**
   * Return all games id for day
   * @return integer[]
   */
  public function getGamesId() {
    $query = \Drupal::entityQuery('game');

    $query->condition('day', $this->id());

    $query->sort('game_date', 'ASC');
    $query->sort('id', 'ASC');

    $ids = $query->execute();

    return $ids;
  }

  /**
   * Return the number of games of the day with score setted
   *
   * @return int
   *   Number of games with score setted
   */
  public function getNbGameWIthScore() {
    $query = \Drupal::entityQuery('game')
      ->condition('day', $this->id())
      ->condition('score_team_1', NULL, 'IS NOT')
      ->condition('score_team_2', NULL, 'IS NOT');
    $ids = $query->execute();
    return count($ids);
  }

  public function label() {
    return $this->get('name')->value;
  }
  
  public function getRenderableLabel() {
    $league = $this->getLeague();
    return [
      '#theme' => 'day-small',
      '#league' => [
        'label' => $league->label(),
        'logo' => $league->getLogo('mini_logo')
      ],
      '#day'=> [
        'label'=> $this->label(),
      ]
    ];
  }

  /**
   * Create pathauto aliases for the day
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   * @param bool $update
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);
    if (\Drupal::moduleHandler()->moduleExists('pathauto')) {
      \Drupal::service('pathauto.generator')->updateEntityAlias($this, 'update');

      $trans = \Drupal::service('transliteration');
      $alias_manager = \Drupal::service('path.alias_manager');
      $alias_storage = \Drupal::service('path.alias_storage');

      $system_path = '/mespronos/day/'.$this->id().'/bet';
      $alias_day = $alias_manager->getAliasByPath('/mespronos/day/'.$this->id());
      $path_alias = str_replace('.html', '', $alias_day).'/pronostiquer.html';
      $urlAlias = $alias_manager->getAliasByPath($system_path);
      if ($urlAlias && $urlAlias != $path_alias) {
        $alias_storage->save($system_path, $path_alias);
      }

      $user_ids = \Drupal::entityQuery('user')->execute();
      $users = \Drupal::entityManager()->getStorage("user")->loadMultiple($user_ids);
      foreach ($users as $user) {
        $system_path = '/mespronos/day/'.$this->id().'/results/user/'.$user->id();
        $path_alias = str_replace('.html', '', $alias_day).'/les-pronos-de-'.$trans->transliterate($user->label()).'.html';
        $urlAlias = $alias_manager->getAliasByPath($system_path);
        if ($urlAlias && $urlAlias != $path_alias) {
          $alias_storage->save($system_path, $path_alias);
        }
      }
    }
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
        'weight' => 3,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['number'] = BaseFieldDefinition::create('integer')
      ->setLabel('Day number')
      ->setRevisionable(TRUE)
      ->setSetting('unsigned', TRUE)
      ->setDisplayOptions('form', array(
        'type' => 'number',
        'weight' => 4,
      ));

    return $fields;
  }
}
