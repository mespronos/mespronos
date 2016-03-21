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
        
        if(count($days) == 0) {return false;}

        return [
          '#theme' => 'table',
          '#rows' => self::parseDays($days,$user,$page_league),
          '#header' => self::getHeader($user),
          '#footer' => self::getFooter(),
          '#cache' => [
            'contexts' => ['user'],
            'tags' => [ 'lastbets','user:'.$user->id()],
          ],
        ];
    }

    public static function getHeader(User $user) {
        if($user->id()>0) {
            return [
                t('Day', array(), array('context' => 'mespronos-block')),
                t('Bets', array(), array('context' => 'mespronos-block')),
                t('Points', array(), array('context' => 'mespronos-block')),
                t('Rank', array(), array('context' => 'mespronos-block')),
                ''
            ];
        }
        else{
            return [
                t('Day', array(), array('context' => 'mespronos-block')),
                ''
            ];
        }
    }

    public static function getFooter() {
        return [];
    }

    public static function parseDays($days,User $user,$page_league) {
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
            if($user->id()>0) {
                $ranking = RankingDay::getRankingForBetter($user,$day->entity);
                $row['data']['games_betted'] = $ranking ? $ranking->getGameBetted() : ' ';
                $row['data']['points'] = $ranking ? $ranking->getPoints() : ' ';
                $row['data']['position'] = $ranking ? $ranking->getPosition() : ' ';
                $row['data']['action'] = Link::fromTextAndUrl(t('Details'),Url::fromRoute('mespronos.lastbetsdetails',['day'=>$day->entity->id()]));
            }
            else {
                $row['data']['action'] = Link::fromTextAndUrl(
                  t('Log in to see your score'),
                  Url::fromRoute('user.login',[],[
                      'query' => [
                        'destination' => Url::fromRoute('mespronos.lastbetsdetails',['day'=>$day->entity->id()])->toString(),
                      ]
                    ]
                  )
                );
            }

            $rows[] = $row;
        }
        return $rows;
    }
}
