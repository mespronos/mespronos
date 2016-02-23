<?php

/**
 * @file
 * Contains \Drupal\mespronos\Controller\LeagueController.
 */

namespace Drupal\mespronos\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\mespronos\Entity\League;
use Drupal\mespronos\Entity\RankingLeague;

/**
 * Class LeagueController.
 *
 * @package Drupal\mespronos\Controller
 */
class LeagueController extends ControllerBase {

  public function index(League $league) {
    $last_bets_controller = new LastBetsController();
    $next_bets_controller = new NextBetsController();
    $last_bets = $last_bets_controller->lastBets($league,100);
    $next_bets = $next_bets_controller->nextBets($league,100);
    $ranking = RankingController::getRankingLeague($league);
    return [
      '#theme' =>'league-details',
      '#last_bets' => $last_bets,
      '#next_bets' => $next_bets,
      '#ranking' => $ranking,
      '#cache' => [
        'contexts' => ['user'],
        'tags' => [ 'user:'.\Drupal::currentUser()->id(),'league:'.$league->id()],
      ],
    ];
  }
  public function indexTitle(League $league) {
    return $league->label();
  }

  public function leaguesList() {

  }
}
