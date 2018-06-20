<?php

/**
 * @file
 * Contains Drupal\mespronos\Controller\BetController.
 */

namespace Drupal\mespronos\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\mespronos\Entity\Bet;
use Drupal\mespronos\Entity\Day;
use Drupal\mespronos\Entity\League;
use Drupal\mespronos\Entity\Game;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\Query\Condition;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Cache\Cache;
use Drupal\user\Entity\User;

/**
 * Provides a list controller for Game entity.
 *
 * @ingroup mespronos
 */
class BetController extends ControllerBase {

  /**
   * Define bets scores for a given game
   * @param \Drupal\mespronos\Entity\Game $game
   * @return boolean
   */
  public static function updateBetsFromGame(Game $game) {
    $bets = $game->getBets();
    $points = $game->getLeague()->getPoints();
    foreach ($bets as $bet) {
      self::updateBetFromGame($bet, $game, $points);
    }
  }

  public static function updateBetFromGame(Bet $bet, Game $game, array $points) {
    $bst1 = $bet->getScoreTeam1();
    $bst2 = $bet->getScoreTeam2();
    $gst1 = $game->getScoreTeam1();
    $gst2 = $game->getScoreTeam2();

    if (!$game->isScoreSetted()) {
      $bet->setPoints(NULL);
    }
    elseif ($bst1 === $gst1 && $bst2 === $gst2) {
      $bet->setPoints($points['points_score_found']);
    }
    elseif (($bst1 === $bst2 && $gst1 === $gst2) || ($bst1 > $bst2 && $gst1 > $gst2) || ($bst1 < $bst2 && $gst1 < $gst2)) {
      $bet->setPoints($points['points_winner_found']);
    }
    else {
      $bet->setPoints($points['points_participation']);
    }

    $bet->save();
  }

  public static function updateBetsForDay(Day $day) {
    $games = $day->getGames();

    foreach ($games as $game) {
      self::updateBetsFromGame($game);
    }
    drupal_set_message(t('Points updated for @nb games', ['@nb' => \count($games)]));
    $response = RankingController::recalculateDay($day);
    return $response;
  }

  public static function updateBetsForLeague(League $league) {
    $games = $league->getGames();
    $nb_game_updated = 0;
    $days_to_update = [];

    $batch = [
      'title' => t('Recount League Points'),
      'operations' => [],
      'finished' => '\Drupal\mespronos\Controller\BetController::updateBetsForLeagueOver',
    ];

    foreach ($games as $game) {
      $batch['operations'][] = ['\Drupal\mespronos\Controller\BetController::updateBetsFromGame', [$game]];
        $nb_game_updated++;
        if (!isset($days_to_update[$game->getDayId()])) {
          $days_to_update[$game->getDayId()] = $game->getDay();
        }
    }
    foreach ($days_to_update as $day) {
      $batch['operations'][] = ['\Drupal\mespronos\Entity\RankingDay::createRanking', [$day]];
    }

    $batch['operations'][] = ['\Drupal\mespronos\Entity\RankingLeague::createRanking', [$league]];
    $batch['operations'][] = ['\Drupal\mespronos\Entity\RankingGeneral::createRanking', []];
    batch_set($batch);
    return batch_process(\Drupal::url('entity.league.collection'));
  }

  public static function updateBetsForLeagueOver($success, $results, $operations) {
    if ($success) {
      $message = t('Ranking recalculate');
    } else {
      $message = t('Finished with an error.');
    }
    drupal_set_message($message);
    Cache::invalidateTags(array('ranking'));
    return new RedirectResponse(\Drupal::url('entity.league.collection'));
  }

  public static function getLastUserBetsTable(User $user, $nb_bets = 20, Day $day = NULL) {
    return \Drupal::service('mespronos.bet_manager')->getRecentBetsForUserTable($user, $nb_bets, $day);

  }

  /**
   * @param \Drupal\user\Entity\User $user
   * @param int $nb_bets
   * @return \Drupal\mespronos\Entity\Bet[]
   */
  public static function getLastUserBets(User $user, $nb_bets = 20) {
    return \Drupal::service('mespronos.bet_manager')->getRecentBetsForUser($user, $nb_bets);
  }

  /**
   * @param \Drupal\user\Entity\User $user
   * @param \Drupal\mespronos\Entity\Game $game
   * @return \Drupal\mespronos\Entity\Bet
   */
  public static function loadForUser(User $user, Game $game) {
    $ids = \Drupal::entityQuery('bet')
      ->condition('game', $game->id())
      ->condition('better', $user->id())
      ->execute();
    if (\count($ids) > 0) {
      return Bet::load(array_pop($ids));
    }
    return Bet::create([]);
  }

  /**
   * Determine number of games left to bet for a given user on a given day
   * @param \Drupal\user\Entity\User $user
   * @param \Drupal\mespronos\Entity\Day $day
   * @return integer number of game left to bet
   */
  public static function betsLeft(\Drupal\user\Entity\User $user, Day $day) {
    $now_date = new \DateTime();
    $now_date->setTimezone(new \DateTimeZone('GMT'));

    $injected_database = Database::getConnection();

    $subquery = $injected_database->select('mespronos__bet', 'b');
    $subquery->leftJoin('mespronos__game', 'g', 'b.game = g.id');
    $subquery->fields('g', ['id']);
    $subquery->condition('g.day', $day->id());
    $subquery->condition('b.better', $user->id());

    $query = $injected_database->select('mespronos__game', 'g');
    $query->addExpression('count(g.id)', 'nb_bet_left');
    $query->condition('g.day', $day->id());
    $query->condition('g.game_date', $now_date->format('Y-m-d\TH:i:s'), '>');
    $query->condition('g.id', $subquery, 'NOT IN');

    $results = $query->execute()->fetchAssoc();
    return $results['nb_bet_left'];
  }

}
