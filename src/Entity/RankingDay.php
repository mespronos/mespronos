<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\RankingDay.
 */

namespace Drupal\mespronos\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\mespronos\Controller\RankingController;
use Drupal\Core\Database\Database;
use Drupal\mespronos\Entity\Base\RankingBase;
use Drupal\mespronos_group\Entity\Group;

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
 *   }
 * )
 */
class RankingDay extends RankingBase {

  public function getBaseTable() {
    return 'mespronos__ranking_day';
  }
  
  public function getEntityRelated() {
    return 'day';
  }

  public function getStorageName() {
    return 'ranking_day';
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
    $day_storage = \Drupal::entityTypeManager()->getStorage('day');
    $day = $day_storage->load($this->get('day')->target_id);
    return $day;
  }

  /**
   * return the position of the user of the user concerned by the ranking instance
   * @return int
   */
  public function getPosition($group = NULL) {
    $injected_database = Database::getConnection();
    $query = $injected_database->select('mespronos__ranking_day', 'rd');
    $query->addField('rd', 'id');
    $query->addField('rd', 'points');
    $query->condition('rd.day', $this->getDayiD());
    if($group) {
      $query->join('user__field_group', 'ug', 'ug.entity_id = rd.better');
      $query->condition('ug.field_group_target_id', $group->id());
    }

    $query->orderBy('points', 'DESC');
    $results = $query->execute()->fetchAllAssoc('id');
    $ranking = $this->determinePosition($results);
    return $ranking;
  }

  /**
   * @return integer
   */
  public static function createRanking(\Drupal\mespronos\Entity\Day $day) {
    self::removeRanking($day);
    $data = self::getData($day);
    RankingController::sortRankingDataAndDefinedPosition($data);
    foreach ($data as $row) {
      $rankingDay = RankingDay::create([
        'better' => $row->better,
        'day' => $day->id(),
        'games_betted' => $row->nb_bet,
        'points' => $row->points,
      ]);
      $rankingDay->save();
    }
    return count($data);
  }

  public static function getData(Day $day) {
    $injected_database = Database::getConnection();
    $query = $injected_database->select('mespronos__bet', 'b');
    $query->addField('b', 'better');
    $query->addExpression('sum(b.points)', 'points');
    $query->addExpression('count(b.id)', 'nb_bet');
    $query->join('mespronos__game', 'g', 'b.game = g.id');
    $query->groupBy('b.better');
    $query->orderBy('points', 'DESC');
    $query->orderBy('nb_bet', 'DESC');
    $query->condition('g.day', $day->id());
    $query->isNotNull('b.points');
    $results = $query->execute()->fetchAllAssoc('better');

    return $results;
  }

  public static function removeRanking(\Drupal\mespronos\Entity\Day $day) {

    $storage = \Drupal::entityTypeManager()->getStorage('ranking_day');
    $query = \Drupal::entityQuery('ranking_day');
    $query->condition('day', $day->id());
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
  public static function getRankingForDay(Day $day, Group $group = null) {
    $storage = \Drupal::entityTypeManager()->getStorage('ranking_day');
    $query = \Drupal::entityQuery('ranking_day');
    $query->condition('day', $day->id());
    if (!is_null($group)) {
      $member_ids = $group->getMembers();
      $query->condition('better', $member_ids, 'IN');
    }
    $query->sort('points', 'DESC');
    $ids = $query->execute();

    $rankings = $storage->loadMultiple($ids);
    return $rankings;
  }

  /**
   * @param \Drupal\user\Entity\User $better
   * @param \Drupal\mespronos\Entity\Day $day
   * @param String $entity_name
   * @param String $storage_name
   * @return \Drupal\mespronos\Entity\RankingDay
   */
  public static function getRankingForBetter(\Drupal\user\Entity\User $better, $day = null, $entity_name = 'day', $storage_name = 'ranking_day') {
    return parent::getRankingForBetter($better, $day, $entity_name, $storage_name);
  }

  /**
   * @param \Drupal\mespronos\Entity\Day $day
   * @param String $entity_name
   * @param String $storage_name
   * @return integer
   */
  public static function getNumberOfBetters($day = null, $entity_name = 'day', $storage_name = 'ranking_day', Group $group = NULL) {

    return parent::getNumberOfBetters($day, $entity_name, $storage_name, $group);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

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

    return $fields;
  }

}
