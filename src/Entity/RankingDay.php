<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\RankingDay.
 */

namespace Drupal\mespronos\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\mespronos\MPNEntityInterface;
use Drupal\user\UserInterface;

/**
 * Defines the RankingDay entity.
 *
 * @ingroup mespronos
 *
 * @ContentEntityType(
 *   id = "ranking_day",
 *   label = @Translation("RankingDay entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\mespronos\Entity\Controller\RankingDayListController",
 *     "views_data" = "Drupal\mespronos\Entity\RankingDayViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\mespronos\Entity\Form\RankingDayForm",
 *       "add" = "Drupal\mespronos\Entity\Form\RankingDayForm",
 *       "edit" = "Drupal\mespronos\Entity\Form\RankingDayForm",
 *       "delete" = "Drupal\mespronos\Entity\Form\RankingDayDeleteForm",
 *     },
 *     "access" = "Drupal\mespronos\RankingDayAccessControlHandler",
 *   },
 *   base_table = "mespronos__ranking_day",
 *   admin_permission = "administer RankingDay entity",
 *   fieldable = FALSE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/ranking_day/{ranking_day}",
 *     "edit-form" = "/admin/ranking_day/{ranking_day}/edit",
 *     "delete-form" = "/admin/ranking_day/{ranking_day}/delete"
 *   },
 *   field_ui_base_route = "ranking_day.settings"
 * )
 */
class RankingDay extends ContentEntityBase implements MPNEntityInterface {

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('better', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('better')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('better')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('better', $uid);
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
  public function getChangedTime() {
    return $this->get('changed')->value;
  }

  /**
   * @return mixed
   */
  public function getDayiD() {
    return $this->get('day')->target_id;
  }

  /**
   * @return Day
   */
  public function getDay() {
    $day_storage = \Drupal::entityManager()->getStorage('day');
    $day = $day_storage->load($this->get('day')->target_id);
    return $day;
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

  public static function createRanking(\Drupal\mespronos\Entity\Day $day) {
    $nb_removed = self::removeRankingDay($day);
  }

  public static function removeRanking(\Drupal\mespronos\Entity\Day $day) {
    $injected_database = Database::getConnection();
    $query = $injected_database->delete('mespronos__ranking_day','rd');
    $query->condition('rd.day',$day->id());

    $results = $query->execute();
    return $results;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the RankingDay entity.'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the RankingDay entity.'))
      ->setReadOnly(TRUE);

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

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
