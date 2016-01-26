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
  public static function getRankingTableForDay(Day $day) {
    $rankingDays = RankingDay::getRankingForDay($day);
    return self::getTableFromRanking($rankingDays);
  }

  public static function getTableFromRanking($rankingDays) {
    $position = 0;
    $next_position = 1;
    $rows = [];
    foreach ($rankingDays  as  $ranking) {
      $position = $next_position;
      if(isset($old_points)) {
        if($old_points == $ranking->get('points')->value) {
          $next_position++;
        }
        else {
          $position++;
        }
      }
      $old_points =  $ranking->get('points')->value;
      $row = [
        'position' => $position,
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