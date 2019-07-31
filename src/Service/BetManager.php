<?php
namespace Drupal\mespronos\Service;

use Drupal\domain\DomainNegotiatorInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\domain\Entity\Domain;
use Drupal\mespronos\Entity\Bet;
use Drupal\mespronos\Entity\Day;
use Drupal\mespronos\Entity\Game;
use Drupal\mespronos_group\Entity\Group;
use Drupal\user\Entity\User;

class BetManager {
  public function getRecentBetsForUserTable(User $user, $nb_bets = 20, Day $day = NULL, $includeGameNotOver = FALSE) {
    $bets = $this->getRecentBetsForUser($user, $nb_bets, $includeGameNotOver);
    $rows = [];
    $leagues = [];
    foreach ($bets as $bet) {
      $game = $bet->getGame(TRUE);
      $day = $game->getDay();
      if (!isset($leagues[$day->getLeagueID()])) {
        $leagues[$day->getLeagueID()] = $day->getLeague();
      }

      $day_renderable = $day->getRenderableLabel();
      $row = [
        'data' => [
          'day' => [
            'data' => render($day_renderable),
            'class' => ['day-cell']
          ],
          $game->labelTeams(),
          $game->labelScore(),
          $bet->label(),
          ['data' => $bet->getPoints(), 'class'=>'points'],
        ],
        'class' => $leagues[$day->getLeagueID()]->getPointsCssClass($bet->getPoints()),
      ];

      $rows[] = $row;
    }

    $header = [
      t('League', array(), array('context' => 'mespronos')),
      t('Game', array(), array('context' => 'mespronos')),
      t('Score', array(), array('context' => 'mespronos')),
      t('Bet', array(), array('context' => 'mespronos')),
      t('Points', array(), array('context' => 'mespronos')),
    ];

    $table_array = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
      '#cache' => [
        'contexts' => ['user'],
        'tags' => ['lastbets', 'user:'.$user->id()],
      ],
    ];

    return $table_array;
  }

  public function getRecentBetsForUser($user, $nb, $includeGameNotOver = FALSE) {

    $now = new \DateTime(null, new \DateTimeZone("UTC"));
    $query = db_select('mespronos__bet', 'b');
    $query->condition('b.better', $user->id());
    $query->join('mespronos__game', 'g', 'g.id = b.game');
    if ($includeGameNotOver) {
      $query->condition('g.game_date', $now->format('Y-m-d\TH:i:s'), '<');
    }
    else {
      $query->isNotNull('b.points');
    }
    $query->orderBy('g.game_date', 'DESC');
    $query->range(0, $nb);
    $query->fields('b', ['id']);
    $ids = $query->execute()->fetchAllKeyed(0,0);
    if (\count($ids) > 0) {
      return Bet::loadMultiple($ids);
    }
    return [];
  }

}
