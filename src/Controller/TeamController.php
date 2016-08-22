<?php

/**
 * @file
 * Contains \Drupal\mespronos\Controller\LeagueController.
 */

namespace Drupal\mespronos\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\mespronos\Entity\League;
use Drupal\mespronos\Entity\Team;

/**
 * Class TeamController.
 *
 * @package Drupal\mespronos\Controller
 */
class TeamController extends ControllerBase {

  public static function getLastResults(Team $team) {
    $last_games = [];
    $games = $team->getLastGames();
    if(count($games) == 0) {
      return;
    }
    else {
      foreach ($games as $game) {
        $team_number = $game->getTeam1Id() == $team->id() ? 1 : 2;
        $winner = $game->getWinner(); {
          if($winner == 'N') {
            $result = t('N',[],['context'=>'game result']);
          }
          elseif ($team_number == $winner) {
            $result = t('V',[],['context'=>'game result']);
          }
          else {
            $result = t('D',[],['context'=>'game result']);
          }
        }
        $last_games[] = [
          '#markup' => '<span class="result result-'.$result.'" title="'.strip_tags($game->labelWithScore()).'">'.$result.'</span>'
        ];
      }
    }
    return [
      '#theme' => 'item_list',
      '#list_type' => 'ol',
      '#items' => $last_games,
      '#attributes' => [
        'class' => 'team-last-results'
      ]
    ];
  }



}
