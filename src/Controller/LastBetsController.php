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

    public function lastBets(League $league = null,$nb = 10) {
        $user = User::load(\Drupal::currentUser()->id());
        $page_league = isset($league);
        $days = DayController::getlastDays($nb,$league);

        return [
          '#theme' => 'table',
          '#rows' => self::parseDays($days,$user,$page_league),
          '#header' => self::getHeader(),
          '#footer' => self::getFooter(),
          '#cache' => [
            'contexts' => ['user'],
            'tags' => [ 'lastbets','user:'.$user->id()],
          ],
        ];
    }

    public static function getHeader() {
        return [
            t('Day',array(),array('context'=>'mespronos-block')),
            t('Bets',array(),array('context'=>'mespronos-block')),
            t('Points',array(),array('context'=>'mespronos-block')),
            t('Rank',array(),array('context'=>'mespronos-block')),
            ''
        ];
    }

    public static function getFooter() {
        return [];
    }

    public static function parseDays($days,User $user,$page_league) {
        $leagues = [];
        $rows = [];
        foreach ($days  as $day_id => $day) {
            $league_id = $day->entity->get('league')->first()->getValue()['target_id'];
            if(!isset($leagues[$league_id])) {
                $leagues[$league_id] = League::load($league_id);
            }
            $league = $leagues[$league_id];
            $ranking = false;
            if($user->id()>0) {
                $ranking = RankingDay::getRankingForBetter($user,$day->entity);
                $action_links = Link::fromTextAndUrl(
                  t('Details'),
                  Url::fromRoute('mespronos.lastbetsdetails',['day'=>$day->entity->id()])
                );
            }
            $row = [
              'data' => [
                'day' => '',
                'games_betted' => $user->id() > 0 && $ranking ? $ranking->getGameBetted() : '/',
                'points' => $user->id() > 0 && $ranking ? $ranking->getPoints() : '/',
                'position' => $user->id() > 0 && $ranking ? $ranking->getPosition() : '/',
                'action' => $ranking ? $action_links : '',
              ]
            ];
            $cell = [];
            if($page_league == null) {
                $cell['link'] = Link::fromTextAndUrl($league->label(),Url::fromRoute('mespronos.league.index',['league'=>$league->id()]))->toRenderable();
                $cell['backtoline'] = ['#markup' => '<br />'];
            }
            $cell['day'] = [
              '#type' => 'markup',
              '#markup' => $day->entity->label(),
            ];
            $row['data']['day'] = render($cell);

            $rows[] = $row;
        }
        return $rows;
    }
}
