<?php

/**
 * @file
 * Contains \Drupal\mespronos\Controller\LeagueController.
 */

namespace Drupal\mespronos\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\mespronos\Entity\Day;
use Drupal\mespronos\Entity\Game;
use Drupal\mespronos\Entity\Bet;
use Drupal\user\Entity\User;

/**
 * Class DayController.
 *
 * @package Drupal\mespronos\Controller
 */
class DayController extends ControllerBase {

  public function index(Day $day, User $user = null) {

    if($user == null) {
      $user = User::load(\Drupal::currentUser()->id());
    }
    $games = Game::getGamesForDay($day);
    $games_ids = $games['ids'];
    $games_entity = $games['entities'];
    $bets = Bet::getUserBetsForGames($games_ids,$user);
    $rows = [];
    foreach($games_entity as $gid => $game) {
      if($user->id() !== \Drupal::currentUser()->id() && !$game->isPassed()) {
        $bet = '?';
      }
      else {
        $bet = isset($bets[$gid]) ? $bets[$gid]->labelBet() : '/';
      }
      $points = isset($bets[$gid]) ? $bets[$gid]->get('points')->value : '/';
      $row = [
        $game->labelTeams(),
        $game->labelScore(),
        $bet,
        $points,
      ];
      $rows[] = $row;
    }

    $header = [
      $this->t('Game',array(),array('context'=>'mespronos')),
      $this->t('Score',array(),array('context'=>'mespronos')),
      $this->t('Bet',array(),array('context'=>'mespronos')),
      $this->t('Points',array(),array('context'=>'mespronos')),
    ];

    $table_array = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
      '#cache' => [
        'contexts' => ['user'],
        'tags' => [ 'lastbets','user:'.$user->id()],
      ],
    ];

    return [
      '#theme' =>'day-details',
      '#last_bets' => $table_array,
      '#ranking' => RankingController::getRankingTableForDay($day),
      '#cache' => [
        'contexts' => ['user'],
        'tags' => [ 'user:'.\Drupal::currentUser()->id().'_'.$user->id(),'lastbets'],
      ],
    ];
  }
  public function indexTitle(Day $day, \Drupal\user\Entity\User $user = null) {
    $league = $day->getLeague();
    if($user == null) {
      return t('My bets on @day',array('@day'=>$league->label().' - '.$day->label()));
    }
    else {
      return t('@user\'s bets on @day',array('@day'=>$league->label().' - '.$day->label(),'@user'=>$user->getUsername()));
    }

  }

}
