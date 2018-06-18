<?php

/**
 * @file
 * Contains Drupal\mespronos\Controller\GameController.
 */

namespace Drupal\mespronos\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\mespronos\Entity\Day;
use Drupal\mespronos\Entity\Game;

/**
 * Provides a controller for Game entity.
 *
 * @ingroup mespronos
 */
class GameController extends ControllerBase {

  /**
   * Return array of games that has no marks setted
   * @param bool $only_past
   * @return \Drupal\mespronos\Entity\Game[]
   */
  public static function getGameWithoutMarks($only_past = true) {
    $game_storage = \Drupal::entityTypeManager()->getStorage('game');
    $query = \Drupal::entityQuery('game');

    if ($only_past) {
      $now = new \DateTime(null, new \DateTimeZone("UTC"));
      $query->condition('game_date', $now->format('Y-m-d\TH:i:s'), '<');
    }

    $group = $query->orConditionGroup()
      ->condition('score_team_1', null, 'is')
      ->condition('score_team_2', null, 'is');
    $query->sort('game_date', 'ASC');
    $ids = $query->condition($group)->execute();

    $games = $game_storage->loadMultiple($ids);

    return $games;
  }

  /**
   * Return all games available to bet on a given day
   * @param \Drupal\mespronos\Entity\Day $day
   * @return \Drupal\mespronos\Entity\Game[]
   */
  public static function getGamesToBet(Day $day) {
    $game_storage = \Drupal::entityTypeManager()->getStorage('game');
    $query = \Drupal::entityQuery('game');

    $now = new \DateTime(null, new \DateTimeZone("UTC"));

    $query->condition('day', $day->id());
    $query->condition('game_date', $now->format('Y-m-d\TH:i:s'), '>');

    $group = $query->orConditionGroup()
      ->condition('score_team_1', null, 'is')
      ->condition('score_team_2', null, 'is');

    $query->sort('game_date', 'ASC');
    $query->sort('id', 'ASC');

    $ids = $query->condition($group)->execute();

    $games = $game_storage->loadMultiple($ids);

    return $games;
  }

  public static function gameTitle(Game $game) {
    return $game->labelTeams();
  }

  public static function getTeamFlags(Game $game) {
    $team1 = $game->getTeam1();
    $team2 = $game->getTeam2();
    return [
      'team1' => ['#markup' => $team1->label(true)],
      'team2' => ['#markup' => $team2->label(true)],
    ];
  }

  public static function getBettersBets(Game $game) {
    if (!$game->isPassed()) {return ["#markup"=> t('Bets will be visible once the game is started')]; }
    $data = self::getBettersBetsData($game);
    $rows = [];
    $header = [
      t('Better', array(), array('context'=>'mespronos')),
      t('bet', array(), array('context'=>'mespronos')),
      t('Points', array(), array('context'=>'mespronos')),
    ];
    $league = $game->getLeague();
    foreach ($data as $better => $bet) {
      $better_entity = \Drupal\user\Entity\User::load($better);
      $better_renderable = UserController::getRenderableUser($better_entity);
      $row = [
        'data' => [
          'better' => [
            'data' => render($better_renderable),
            'class' => ['better-cell']
          ],
          $bet->score_team_1.' - '.$bet->score_team_2,
          ['data' => $bet->points, 'class'=>'points'],
        ],
        'class' => $league->getPointsCssClass($bet->points),
      ];
      $rows[] = $row;
    }
    $table_array = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
      '#cache' => [
        'contexts' => ['user'],
        'tags' => ['game:'.$game->id()],
      ],
    ];

    return [
      'table' => $table_array,
    ];
  }

  public static function getBettersBetsData(Game $game) {
    $query = \Drupal::database()->select('mespronos__bet', 'b');
    $query->join('users', 'u', 'b.better = u.uid');
    $query->join('users_field_data', 'ufd', 'ufd.uid = u.uid');
    $query->fields('b', ['better', 'score_team_1', 'score_team_2', 'points']);
    $query->condition('b.game', $game->id());
    $query->orderBy('b.points', 'DESC');
    $query->orderBy('ufd.name');
    $results = $query->execute()->fetchAllAssoc('better');
    return $results;
  }

}
