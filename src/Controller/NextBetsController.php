<?php

/**
 * @file
 * Contains Drupal\mespronos\Controller\NextBetsController.
 */

namespace Drupal\mespronos\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\mespronos\Entity\League;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\user\Entity\User;

/**
 * Class NextBetsController.
 *
 * @package Drupal\mespronos\Controller
 */
class NextBetsController extends ControllerBase {

  public function nextBets(League $league = NULL, $nb = 10, $mode = 'PAGE') {
    $user = User::load(\Drupal::currentUser()->id());
    $user_uid = $user->id();
    $days = DayController::getNextDaysToBet($nb, $league);
    if(\count($days) === 0) {
      return [];
    }
    $build = [
      '#cache' => [
        'contexts' => ['user'],
        'tags' => ['user:' . $user_uid, 'nextbets'],
        'max-age' => '600',
      ],
    ];
    foreach ($days as $day) {
      /** @var \Drupal\mespronos\Entity\Day $dayEntity */
      $dayEntity = $day->entity;
      $league = $dayEntity->getLeague();
      $game_date = \DateTime::createFromFormat('Y-m-d\TH:i:s', $day->day_date, new \DateTimeZone("GMT"));
      $game_date->setTimezone(new \DateTimeZone("Europe/Paris"));
      $now_date = new \DateTime();

      $i = $game_date->diff($now_date);
      $bets_left = BetController::betsLeft($user, $dayEntity);


      $time_left = $i->format('%a') > 0 ? t('@d days', [
        '@d' => $i->format('%a'),
        '@G' => $i->format('%H'),
        '@i' => $i->format('%I'),
      ]) : t('@GH@im', ['@G' => $i->format('%H'), '@i' => $i->format('%I')]);

      $build[$day->entity->id()] = [
        '#theme' => 'day-to-bet',
        '#day' => $day,
        '#league_logo' => $league->getLogo('mespronos_bloc_aside'),
        '#time_left' => $time_left,
        '#nb_bet_left' => $bets_left,
      ];
    }

    return $build;
  }

}
