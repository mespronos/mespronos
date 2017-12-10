<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\Game.
 */

namespace Drupal\mespronos\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\mespronos\Entity\Base\MPNContentEntityBase;
use Drupal\mespronos\Entity\Getters\GameGettersTrait;
use Drupal\mespronos\Entity\Traits\ScoreTeamTrait;
use Drupal\mespronos\Entity\Interfaces\MPNEntityInterface;
use Drupal\mespronos\Controller\BetController;

/**
 * Defines the Game entity.
 *
 * @ingroup mespronos
 *
 * @ContentEntityType(
 *   id = "game",
 *   label = @Translation("Game entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\mespronos\Entity\Controller\GameListController",
 *     "views_data" = "Drupal\mespronos\Entity\ViewsData\GameViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\mespronos\Entity\Form\GameForm",
 *       "add" = "Drupal\mespronos\Entity\Form\GameForm",
 *       "edit" = "Drupal\mespronos\Entity\Form\GameForm",
 *       "delete" = "Drupal\mespronos\Entity\Form\MPNEntityDeleteForm",
 *       "remove_bets" = "Drupal\mespronos\Entity\Form\GameRemoveBetsForm",
 *     },
 *     "access" = "Drupal\mespronos\ControlHandler\GameAccessControlHandler",
 *   },
 *   base_table = "mespronos__game",
 *   admin_permission = "administer Game entity",
 *   fieldable = FALSE,
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/mespronos/game/{group}",
 *     "edit-form" = "/admin/game/{game}/edit",
 *     "delete-form" = "/admin/game/{game}/delete",
 *     "remove-bets" = "/admin/game/{game}/remove-bets"
 *   },
 *   field_ui_base_route = "game.settings"
 * )
 */
class Game extends MPNContentEntityBase implements MPNEntityInterface {

  use GameGettersTrait;
  use ScoreTeamTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += array(
      'user_id' => \Drupal::currentUser()->id(),
    );
  }

  public function save() {
    $return = parent::save();
    if ($this->isScoreSetted()) {
      BetController::updateBetsFromGame($this);
    }
    return $return;
  }

  public function isPassed() {
    $game_date = \DateTime::createFromFormat('Y-m-d\TH:i:s', $this->getGameDate(), new \DateTimeZone("GMT"));
    $game_date->setTimezone(new \DateTimeZone("Europe/Paris"));
    $now = new \DateTime(NULL, new \DateTimeZone("UTC"));
    return($game_date < $now);
  }

  public static function getGamesForDay(Day $day) {
    $query = \Drupal::entityQuery('game');
    $query->condition('day', $day->id());
    $query->sort('game_date', 'ASC');
    $query->sort('id', 'ASC');
    $ids = $query->execute();
    $return = [
      'ids' => $ids,
      'entities' => self::loadMultiple($ids),
    ];
    return $return;
  }

  /**
   * @param $number
   * @return \Drupal\mespronos\Entity\Game[]
   */
  public static function getLastestGamesWithMark($number) {
    $query = \Drupal::entityQuery('game');
    $query->condition('score_team_1', 0, '>=');
    $query->condition('score_team_2', 0, '>=');
    $query->sort('game_date', 'DESC');
    $query->sort('id', 'ASC');
    $query->range(0, $number);
    $ids = $query->execute();
    return self::loadMultiple($ids);

  }

  /**
   * Remove bets on current day
   *
   * @return integer number of deleted bets
   */
  public function removeBets() {
    $query = \Drupal::entityQuery('bet');
    $query->condition('game', $this->id());
    $ids = $query->execute();
    $bets = Bet::loadMultiple($ids);
    foreach ($bets as $bet) {
      $bet->delete();
    }
    \Drupal::logger('mespronos')->notice(t('Bets removed on game #@id (@game_label) : @nb_bets removed', [
      '@id'=>$this->id(),
      '@game_label'=>$this->label(),
      '@nb_bets' => \count($ids),
    ]));
    return \count($ids);
  }

  public function setScore($score_team_1, $score_team_2) {
    $this->set('score_team_1', $score_team_1);
    $this->set('score_team_2', $score_team_2);
    return $this;
  }

  public function getBaseTable() {
    return 'mespronos__game';
  }

  public function getDataTable() {
    return 'mespronos__game';
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['creator'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of the Game entity author.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDefaultValueCallback('Drupal\node\Entity\Node::getCurrentUserId')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'author',
        'weight' => -2,
      ))
      ->setDisplayConfigurable('form', false)
      ->setDisplayConfigurable('view', false);

    $fields['day'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Day'))
      ->setDescription(t('Day entity reference'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'day')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'entity_reference',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'options_select',
        'weight' => -1,
        'settings' => array(),
      ))
      ->setDisplayConfigurable('form', false)
      ->setDisplayConfigurable('view', TRUE);

    $fields['team_1'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Receving Team'))
      ->setDescription(t('Hosting team'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'team')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'entity_reference',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'options_select',
        'weight' => 2,
        'settings' => array(),
      ))
      ->setDisplayConfigurable('form', false)
      ->setDisplayConfigurable('view', TRUE);

    $fields['team_2'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Guest Team'))
      ->setDescription(t('Second team'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'team')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'entity_reference',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'options_select',
        'weight' => 3,
        'settings' => array(),
      ))
      ->setDisplayConfigurable('form', false)
      ->setDisplayConfigurable('view', TRUE);

    $fields['score_team_1'] = BaseFieldDefinition::create('integer')
      ->setLabel('Score Team 1')
      ->setRevisionable(TRUE)
      ->setSetting('unsigned', TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'weight' => 4,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'number',
        'weight' => 4,
      ));

    $fields['score_team_2'] = BaseFieldDefinition::create('integer')
      ->setLabel('Score Team 2')
      ->setRevisionable(TRUE)
      ->setSetting('unsigned', TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'weight' => 5,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'number',
        'weight' => 5,
      ));

    $fields['game_date'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('Date'))
      ->setDescription(t('The game\'s date'))
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
        'label' => 'hidden',
        'type' => 'datetime_default',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'datetime_default',
        'weight' => 1,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);



    return $fields;
  }

}
