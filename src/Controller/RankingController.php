<?php
namespace Drupal\mespronos\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\mespronos\Entity\RankingDay;
use Drupal\mespronos\Entity\Day;

/**
 * Class DefaultController.
 *
 * @package Drupal\mespronos\Controller
 */
class RankingController extends ControllerBase {

  public static function recalculateDay($day) {
    $day_storage = \Drupal::entityManager()->getStorage('day');
    $day = $day_storage->load($day);
    $nb_updates = RankingDay::createRanking($day);
    drupal_set_message(t('Ranking updated for @nb betters',array('@nb'=>$nb_updates)));
    return new RedirectResponse(\Drupal::url('entity.day.list'));
  }

  public static function sortRankingDataAndDefinedPosition(&$data) {
    usort($data,function($item1, $item2) {
      if (intval($item1->points) == intval($item2->points)) return 0;
      return intval($item1->points) > intval($item2->points) ? -1 : 1;
    });
    $next_position = 1;
    foreach($data as &$value) {
      if(isset($old_object) && $old_object->points == $value->points) {
        $value->position = $old_object->position;
      }
      else {
        $value->position = $next_position;
      }
      $next_position++;
      $old_object = $value;
    }
    return $data;

  }

  public static function getRankingTableForDay(Day $day) {
    $rankingDays = RankingDay::getRankingForDay($day);
    return self::getTableFromRanking($rankingDays);
  }

  public static function getTableFromRanking($rankingDays) {
    $rows = [];
    foreach ($rankingDays  as  $ranking) {;
      $row = [
        'position' => $ranking->get('position')->value,
        'better' => $ranking->getOwner()->getUsername(),
        'points' => $ranking->get('points')->value,
        'games_betted' => $ranking->get('games_betted')->value,
      ];
      $rows[] = $row;
    }
    $header = [
      t('Position',array(),array('context'=>'mespronos')),
      t('Better',array(),array('context'=>'mespronos')),
      t('Points',array(),array('context'=>'mespronos')),
      t('Games betted',array(),array('context'=>'mespronos')),
    ];
    return [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];
  }


}