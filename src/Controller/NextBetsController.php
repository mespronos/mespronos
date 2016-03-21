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

    public function nextBets(League $league = null,$nb=10) {
        $user = User::load(\Drupal::currentUser()->id());
        $user_uid =  $user->id();
        $days = DayController::getNextDaysToBet($nb,$league);
        $page_league = isset($league);

        if(count($days) == 0) {return false;}

        return [
          '#theme' => 'table',
          '#rows' => self::parseDays($days,$user,$page_league),
          '#header' => self::getHeader(),
          '#footer' => self::getFooter(),
          '#cache' => [
            'contexts' => ['user'],
            'tags' => [ 'user:'.$user_uid,'nextbets'],
            'max-age' => '120',
          ],
        ];
    }

    public static function getHeader() {
        return [
            t('Day',array(),array('context'=>'mespronos-block')),
            t('Games',array(),array('context'=>'mespronos-block')),
            t('Bets left',array(),array('context'=>'mespronos-block')),
            t('Time left',array(),array('context'=>'mespronos-block')),
            '',
        ];
    }

    public static function getFooter() {
        return [];
    }

    public static function parseDays($days,User $user,$page_league) {
        $rows = [];
        foreach ($days  as $day_id => $day) {
            $game_date = \DateTime::createFromFormat('Y-m-d\TH:i:s',$day->day_date,new \DateTimeZone("GMT"));
            $game_date->setTimezone(new \DateTimeZone("Europe/Paris"));
            $now_date = new \DateTime();

            $i = $game_date->diff($now_date);
            $bets_left = BetController::betsLeft($user,$day->entity);

            $day_renderable = $day->entity->getRenderableLabel();

            $row = [
              'data' => [
                'day' => [
                  'data' => render($day_renderable),
                  'class' => ['day-cell']
                ],
                'nb_game' => $day->nb_game,
                'bets_left' => $bets_left,
                'time_left' => $i->format('%a') >0 ? t('@d days, @GH@im',array('@d'=>$i->format('%a'),'@G'=>$i->format('%H'),'@i'=>$i->format('%i'))) : t('@GH@im',array('@G'=>$i->format('%H'),'@i'=>$i->format('%i'))),
                'action' => '',
              ]
            ];

            if($user->id()>0) {
                if($bets_left > 0) {
                    $text = t('Bet',[],['context'=>'mespronos-nextbet']);
                }
                else {
                    $text = t('Edit',[],['context'=>'mespronos-nextbet']);
                }
                $row['data']['action'] = Link::fromTextAndUrl(
                  $text,
                  new Url('mespronos.day.bet', ['day' => $day_id],['query' => ['destination' => \Drupal::service('path.current')->getPath()]])
                );
            }
            else {
                $row['data']['action'] = Link::fromTextAndUrl(
                  t('Log in'),
                  Url::fromRoute('mespronos.login',[],['query' => ['destination' => Url::fromRoute('mespronos.day.bet', ['day' => $day_id])->toString()]])
                );
            }
            $rows[] = $row;
        }
        return $rows;
    }
}
