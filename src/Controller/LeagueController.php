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
    $betController = new BettingController();
    $last_bets = $betController->lastBets($league);
    $next_bets = $betController->nextBets($league);
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

}
