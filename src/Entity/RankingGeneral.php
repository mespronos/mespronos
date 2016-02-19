<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\RankingGeneral.
 */

namespace Drupal\mespronos\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\mespronos\Controller\RankingController;
use Drupal\Core\Database\Database;

/**
 * Defines the RankingGeneral entity.
 *
 * @ingroup mespronos
 *
 * @ContentEntityType(
 *   id = "ranking_general",
 *   label = @Translation("RankingGeneral entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\mespronos\Entity\Controller\RankingGeneralListController",
 *     "views_data" = "Drupal\mespronos\Entity\ViewsData\RankingGeneralViewsData",
 *     "access" = "Drupal\mespronos\ControlHandler\RankingGeneralAccessControlHandler",
 *   },
 *   base_table = "mespronos__ranking_general",
 *   admin_permission = "administer RankingGeneral entity",
 *   fieldable = FALSE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid"
 *   }
 * )
 */
class RankingGeneral extends Ranking {

  public static function createRanking() {
    self::removeRanking();
    $data = self::getData();
    RankingController::sortRankingDataAndDefinedPosition($data);
    foreach($data as $row) {
      $rankingLeague = self::create([
        'better' => $row->better,
        'games_betted' => $row->nb_bet,
        'points' => $row->points,
        'position' => $row->position,
      ]);
      $rankingLeague->save();
    }
    return count($data);
  }

  public static function getData() {
    $injected_database = Database::getConnection();
    $query = $injected_database->select('mespronos__ranking_league','rl');
    $query->addField('rl','better');
    $query->addExpression('sum(rl.points)','points');
    $query->addExpression('sum(rl.games_betted)','nb_bet');
    $query->join('mespronos__league','l','l.id = rl.league');
    $query->groupBy('rl.better');
    $query->orderBy('points','DESC');
    $query->orderBy('nb_bet','DESC');
    $query->condition('l.status',array('active','over'),'IN');
    $results = $query->execute()->fetchAllAssoc('better');

    return $results;
  }

  public static function removeRanking() {
    $storage = \Drupal::entityManager()->getStorage('ranking_general');
    $query = \Drupal::entityQuery('ranking_general');
    $ids = $query->execute();

    $rankings = $storage->loadMultiple($ids);
    $nb_deleted = count($rankings);
    foreach ($rankings as $ranking) {
      $ranking->delete();
    }
    return $nb_deleted;
  }

  /**
   * @return \Drupal\mespronos\Entity\RankingGeneral
   */
  public static function getRanking($entity = null,$entity_name='general',$storage_name='ranking_general') {
    return parent::getRanking(null,$entity_name,$storage_name);
  }

  /**
   * @param Drupal\user\Entity\User
   * @param String $type
   * @return \Drupal\mespronos\Entity\RankingGeneral
   */
  public static function getRankingForBetter(\Drupal\user\Entity\User $better,$entity = null,$entity_name=null,$storage_name=null) {
    return parent::getRankingForBetter($better,$entity,$entity_name,'ranking_general');
  }

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    return $fields;
  }

}
