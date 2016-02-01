<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\RankingDay.
 */

namespace Drupal\mespronos\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\mespronos\Controller\RankingController;
use Drupal\mespronos\MPNEntityInterface;
use Drupal\Core\Database\Database;

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
 *     "views_data" = "Drupal\mespronos\Entity\ViewsData\RankingDayViewsData",
 *     "access" = "Drupal\mespronos\ControlHandler\RankingDayAccessControlHandler",
 *   },
 *   base_table = "mespronos__ranking_day",
 *   admin_permission = "administer RankingDay entity",
 *   fieldable = FALSE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid"
 *   },
 *   field_ui_base_route = "ranking_day.settings"
 * )
 */
class RankingDay extends Ranking implements MPNEntityInterface {

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

  /**
   * @return integer
   */
  public static function createRanking(\Drupal\mespronos\Entity\Day $day) {
    self::removeRanking($day);
    $data = self::getData($day);
    RankingController::sortRankingDataAndDefinedPosition($data);
    foreach($data as $row) {
      $rankingDay = RankingDay::create([
        'better' => $row->better,
        'day' => $day->id(),
        'games_betted' => $row->nb_bet,
        'points' => $row->points,
        'position' => $row->position,
      ]);
      $rankingDay->save();
      $league = $day->getLeague();
      RankingLeague::createRanking($league);
    }
    return count($data);
  }

  public static function getData(Day $day) {
    $injected_database = Database::getConnection();
    $query = $injected_database->select('mespronos__bet','b');
    $query->addField('b','better');
    $query->addExpression('sum(b.points)','points');
    $query->addExpression('count(b.id)','nb_bet');
    $query->join('mespronos__game','g','b.game = g.id');
    $query->groupBy('b.better');
    $query->orderBy('points','DESC');
    $query->orderBy('nb_bet','DESC');
    $query->condition('g.day',$day->id());
    $query->isNotNull('b.points');
    $results = $query->execute()->fetchAllAssoc('better');

    return $results;
  }

  public static function removeRanking(\Drupal\mespronos\Entity\Day $day) {

    $storage = \Drupal::entityManager()->getStorage('ranking_day');
    $query = \Drupal::entityQuery('ranking_day');
    $query->condition('day',$day->id());
    $ids = $query->execute();

    $rankings = $storage->loadMultiple($ids);
    $nb_deleted = count($rankings);
    foreach ($rankings as $ranking) {
      $ranking->delete();
    }

    return $nb_deleted;
  }

  /**
   * @param \Drupal\mespronos\Entity\Day $day
   * @return \Drupal\mespronos\Entity\RankingDay[]
   */
  public static function getRankingForDay(Day $day) {
    $storage = \Drupal::entityManager()->getStorage('ranking_day');
    $query = \Drupal::entityQuery('ranking_day');
    $query->condition('day', $day->id());
    $query->sort('position','ASC');
    $ids = $query->execute();

    $rankings = $storage->loadMultiple($ids);
    return $rankings;
  }


  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = [];
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

    $fields['position'] = BaseFieldDefinition::create('integer')
      ->setLabel('Position')
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
