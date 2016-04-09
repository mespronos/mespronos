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
class RankingDay extends Ranking {

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
   * @param \Drupal\user\Entity\User $better
   * @param \Drupal\mespronos\Entity\Day $day
   * @param String $entity_name
   * @param String $storage_name
   * @return \Drupal\mespronos\Entity\RankingDay
   */
  public static function getRankingForBetter(\Drupal\user\Entity\User $better,$day = null,$entity_name='day',$storage_name='ranking_day') {
    return parent::getRankingForBetter($better,$day,$entity_name,$storage_name);
  }

  /**
   * @param \Drupal\mespronos\Entity\Day $day
   * @param String $entity_name
   * @param String $storage_name
   * @return integer
   */
  public static function getNumberOfBetters($day = null,$entity_name='day',$storage_name='ranking_day') {
    return parent::getNumberOfBetters($day,$entity_name,$storage_name);
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
