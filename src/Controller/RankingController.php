<?php
namespace Drupal\mespronos\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\mespronos\Entity\League;
use Drupal\mespronos\Entity\Ranking;
use Drupal\mespronos\Entity\RankingDay;
use Drupal\mespronos\Entity\RankingLeague;
use Drupal\mespronos\Entity\RankingGeneral;
use Drupal\mespronos\Entity\Day;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Class DefaultController.
 *
 * @package Drupal\mespronos\Controller
 */
class RankingController extends ControllerBase {

  /**
   * @param \Drupal\mespronos\Entity\Day $day
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public static function recalculateDay(Day $day) {
    $nb_updates = RankingDay::createRanking($day);
    RankingLeague::createRanking($day->getLeague());
    RankingGeneral::createRanking();
    drupal_set_message(t('Ranking updated for @nb betters',array('@nb'=>$nb_updates)));
    Cache::invalidateTags(array('ranking'));
    return new RedirectResponse(\Drupal::url('entity.day.collection'));
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
  
  public static function getRankingLeague(League $league) {
    $ranking = RankingLeague::getRanking($league);
    return self::getTableFromRanking($ranking);
  }

  public static function getRankingTableForDay(Day $day) {
    $rankingDays = RankingDay::getRankingForDay($day);
    return self::getTableFromRanking($rankingDays);
  }

  /**
   * @param Ranking[] $rankings
   * @return array
   */
  public static function getTableFromRanking($rankings) {
    $user = \Drupal::currentUser();
    $rows = [];
    $old_rank = null;
    foreach ($rankings  as  $ranking) {
      $better =$ranking->getOwner();
      $picture = UserController::getUserPictureAsRenderableArray($better,'mini_thumbnail');
      $row = [
        'data' => [
          'position' => $ranking->get('position')->value != $old_rank ? $ranking->get('position')->value : '-',
          'picture' => render($picture),
          'better' => $ranking->getOwner()->getUsername(),
          'points' => $ranking->get('points')->value,
          'games_betted' => $ranking->get('games_betted')->value,
        ]
      ];
      $old_rank = $ranking->get('position')->value;
      if($ranking instanceof RankingDay) {
        $row['data']['better'] = Link::fromTextAndUrl(
          $ranking->getOwner()->getUsername(),
          Url::fromRoute('mespronos.lastbetsdetailsforuser',['day'=>$ranking->getDayiD(),'user'=>$ranking->getOwner()->id()])
        );
      }
      if($ranking->getOwner()->id() == $user->id()) {
        $row['class'] = ['highlighted','bold'];
      }
      $rows[] = $row;
    }
    $header = [
      t('Rank',array(),array('context'=>'mespronos-ranking')),
      '',
      t('Better',array(),array('context'=>'mespronos-ranking')),
      t('Points',array(),array('context'=>'mespronos-ranking')),
      t('Bets',array(),array('context'=>'mespronos-ranking')),
    ];
    return [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];
  }


}