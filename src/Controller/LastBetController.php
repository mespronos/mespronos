<?php

/**
 * @file
 * Contains Drupal\mespronos\Controller\DefaultController.
 */

namespace Drupal\mespronos\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\mespronos\Entity\League;
use Drupal\mespronos\Entity\Day;
use Drupal\mespronos\Entity\RankingDay;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\user\Entity\User;

/**
 * Class LastBetController.
 *
 * @package Drupal\mespronos\Controller
 */
class LastBetController extends ControllerBase {

    public function lastBets(League $league = null,$nb = 10) {
        $user = User::load(\Drupal::currentUser()->id());
        $user_uid =  $user->id();
        $days = DayController::getlastDays($nb,$league);
        $page_league = isset($league);
        $rows = [];
        $leagues = [];

        foreach ($days  as $day_id => $day) {
            $league_id = $day->entity->get('league')->first()->getValue()['target_id'];
            if(!isset($leagues[$league_id])) {
                $leagues[$league_id] = League::load($league_id);
            }
            $league = $leagues[$league_id];

            if($user_uid>0) {
                $ranking = RankingDay::getRankingForBetter($user,$day->entity);
                $action_links = Link::fromTextAndUrl(
                  t('Details'),
                  Url::fromRoute('mespronos.lastbetsdetails',['day'=>$day->entity->id()])
                );
            }
            else {
                $ranking = false;
                $action_links = Link::fromTextAndUrl(
                  t('Log in to see your score'),
                  Url::fromRoute('user.login',[],[
                      'query' => [
                        'destination' => Url::fromRoute('mespronos.lastbetsdetails',['day'=>$day->entity->id()])->toString(),
                      ]
                    ]
                  )
                );
            }
            $row = [
              'data' => [
                'day' => '',
                'games_betted' => $user_uid > 0 && $ranking ? $ranking->getGameBetted() : '/',
                'points' => $user_uid > 0 && $ranking ? $ranking->getPoints() : '/',
                'position' => $user_uid > 0 && $ranking ? $ranking->getPosition() : '/',
                'action' => $action_links,
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
        $header = [
          $this->t('Day',array(),array('context'=>'mespronos-block')),
          $this->t('Bets',array(),array('context'=>'mespronos-block')),
          $this->t('Points',array(),array('context'=>'mespronos-block')),
          $this->t('Rank',array(),array('context'=>'mespronos-block')),
          ''
        ];

        $footer = [];

        return [
          '#theme' => 'table',
          '#rows' => $rows,
          '#header' => $header,
          '#footer' => $footer,
          '#cache' => [
            'contexts' => ['user'],
            'tags' => [ 'lastbets','user:'.$user_uid],
          ],
        ];
    }

}
