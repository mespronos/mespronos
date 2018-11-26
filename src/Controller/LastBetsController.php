<?php

/**
 * @file
 * Contains Drupal\mespronos\Controller\LastBetsController.
 */

namespace Drupal\mespronos\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\mespronos\Entity\League;
use Drupal\mespronos\Entity\RankingDay;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\user\Entity\User;

/**
 * Class LastBetsController.
 *
 * @package Drupal\mespronos\Controller
 */
class LastBetsController extends ControllerBase {

  public function lastBets(League $league = null, $nb = 10, $mode = 'PAGE', User $user = null, $include_archived = false) {
    if (!$user) {
      $user = User::load(\Drupal::currentUser()->id());
    }
    $cache_tag = 'user:' . $user->id();
    if($group = \Drupal::service('mespronos.domain_manager')->getGroupFromDomain()) {
      $cache_tag .= ':group:' . $group->id();
    }
    else {
      $group = NULL;
    }
    $days = DayController::getlastDays($nb, $league, $include_archived);
    if(\count($days) === 0) {
      return [];
    }
    $build = [
      '#cache' => [
        'contexts' => ['user'],
        'tags' => ['lastbets', $cache_tag],
      ],
    ];

    foreach ($days as $day) {
      /** @var \Drupal\mespronos\Entity\Day $dayEntity */
      $dayEntity = $day->entity;
      $league = $dayEntity->getLeague();
      $ranking = $user->id() > 0 ?  RankingDay::getRankingForBetter($user, $day->entity) : FALSE;

      $build[$day->entity->id()] = [
        '#theme' => 'day-past',
        '#day' => $day,
        '#nb_game' => $dayEntity->getNbGameWIthScore(),
        '#league_logo' => $league->getLogo('mespronos_bloc_aside'),
        '#ranking' => $ranking ? $ranking->getPosition($group) : '-',
        '#points' => $ranking ? $ranking->getPoints() : '-',
        '#logged_user' => $user->id() > 0,
        '#nb_betters' => RankingDay::getNumberOfBetters($day->entity, 'day', 'ranking_day', $group),
      ];
    }

    return $build;
  }

    public static function getHeader(User $user) {
        if ($user->id() > 0) {
            return [
                [
                  'data'=> t('Day', array(), array('context' => 'mespronos-block')),
                  'title' => t('Day', array(), array('context' => 'mespronos-block')),
                ],
                [
                  'data'=> t('Bets', array(), array('context' => 'mespronos-block')),
                  'title' => t('Day', array(), array('context' => 'mespronos-block')),
                  'class' => array(RESPONSIVE_PRIORITY_MEDIUM),
                ],
                [
                  'data'=> t('Points', array(), array('context' => 'mespronos-block')),
                  'title' => t('Day', array(), array('context' => 'mespronos-block')),
                ],
                [
                  'data'=> t('Rank.', array(), array('context' => 'mespronos-block')),
                  'title' => t('Your rank / Number of betters', array(), array('context' => 'mespronos-block')),
                ],
                [
                  'data'=> '',
                  //'class' => array(RESPONSIVE_PRIORITY_MEDIUM),
                ],
            ];
        }
        else {
            return [
                t('Day', array(), array('context' => 'mespronos-block')),
                t('Your results'),
                '',
            ];
        }
    }

    public static function getFooter() {
        return [];
    }

  public static function parseDays($days, User $user, $page_league , $group) {
    if(!$group) {
      $group = NULL;
    }
    $rows = [];
    foreach ($days  as $day_id => $day) {
      $day_renderable = $day->entity->getRenderableLabel();

      $row = [
        'data' => [
          'day' => [
            'data' => render($day_renderable),
            'class' => ['day-cell']
          ],
        ]
      ];
      if ($user->id() > 0) {
          $ranking = RankingDay::getRankingForBetter($user, $day->entity);
          $row['data']['games_betted'] = $ranking ? $ranking->getGameBetted() : ' ';
          $row['data']['points'] = $ranking ? $ranking->getPoints() : ' ';
          $row['data']['position'] = $ranking ? t('<strong>@class</strong> / @nb_better', [
            '@class' => $ranking->getPosition($group),
            '@nb_better' => RankingDay::getNumberOfBetters($day->entity, 'day', 'ranking_day', $group)
          ]) : ' ';
      }
      else {
          $row['data']['action_login'] = Link::fromTextAndUrl(
            t('Log in to see your score'),
            Url::fromRoute('user.login', [], [
                'query' => [
                  'destination' => Url::fromRoute('entity.day.canonical', ['day' => $day->entity->id()])->toString(),
                ]
              ]
            )
          );
      }
      if ($user->id() == \Drupal::currentUser()->id()) {
        $link_details = Url::fromRoute('entity.day.canonical', ['day' => $day->entity->id()])->toString();
      }
      else {
        $link_details = Url::fromRoute('mespronos.lastbetsdetailsforuser', ['user' => $user->id(), 'day' => $day->entity->id()])->toString();
      }
      $cell_details = ['#markup' => '<a class="picto" href="' . $link_details . '" title="' . t('See games and results') . '"><i class="fa fa-list" aria-hidden="true"></i></a>'];
      $row['data']['action'] = ['data' => render($cell_details), 'class' => 'picto'];
      $rows[] = $row;
    }
    return $rows;
  }

}
