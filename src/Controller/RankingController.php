<?php
namespace Drupal\mespronos\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\mespronos\Entity\RankingDay;
use Drupal\mespronos\Entity\RankingLeague;
use Drupal\mespronos\Entity\RankingGeneral;
use Drupal\mespronos\Entity\Day;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Cache\Cache;

/**
 * Class DefaultController.
 *
 * @package Drupal\mespronos\Controller
 */
class RankingController extends ControllerBase {

  /**
   * @param \Drupal\mespronos\Entity\Day $day
   * @return \Drupal\mespronos\Controller\RedirectResponse
   */
  public static function recalculateDay($day) {
    $day_storage = \Drupal::entityManager()->getStorage('day');
    $day = $day_storage->load($day);
    $nb_updates = RankingDay::createRanking($day);
    RankingLeague::createRanking($day->getLeague());
    RankingGeneral::createRanking();
    drupal_set_message(t('Ranking updated for @nb betters',array('@nb'=>$nb_updates)));
    Cache::invalidateTags(array('ranking'));
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

  public static function getRankingGeneral() {
    $ranking = RankingGeneral::getRanking();
    return self::getTableFromRanking($ranking);
  }

  public static function getRankingTableForDay(Day $day) {
    $rankingDays = RankingDay::getRankingForDay($day);
    return self::getTableFromRanking($rankingDays);
  }


  public static function getTableFromRanking($rankingDays) {
    $user = \Drupal::currentUser();
    $rows = [];
    foreach ($rankingDays  as  $ranking) {
      $row = [
        'data' => [
          'position' => $ranking->get('position')->value,
          'better' => $ranking->getOwner()->getUsername(),
          'points' => $ranking->get('points')->value,
          'games_betted' => $ranking->get('games_betted')->value,
        ]
      ];
      if($ranking->getOwner()->id() == $user->id()) {
        $row['class'] = ['highlighted','bold'];
      }
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