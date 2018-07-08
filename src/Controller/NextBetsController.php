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

    public function nextBets(League $league = null, $nb = 10, $mode = 'PAGE') {
        $user = User::load(\Drupal::currentUser()->id());
        $user_uid = $user->id();
        $days = DayController::getNextDaysToBet($nb, $league);
        $page_league = isset($league);

        if (count($days) == 0) {
          if($mode === 'PAGE') {
            $return['next-bet'] = [
              '#markup' => '<p>' . t('No bet for now') . '</p>'
            ];
            $return['#cache'] = [
              'contexts' => ['user'],
              'tags' => ['user:' . $user_uid, 'nextbets'],
              'max-age' => '600',
            ];
            return $return;
          }
          return FALSE;
        }

        return [
          '#theme' => 'table',
          '#rows' => self::parseDays($days, $user, $page_league),
          '#header' => self::getHeader(),
          '#footer' => self::getFooter(),
          '#cache' => [
            'contexts' => ['user'],
            'tags' => ['user:'.$user_uid, 'nextbets'],
            'max-age' => '600',
          ],
        ];
    }

    public static function getHeader() {
        return [
            [
                'data' => t('Day', array(), array('context'=>'mespronos-block')),
            ],
            [
                'data' => t('Games', array(), array('context'=>'mespronos-block')),
                'class' => array(RESPONSIVE_PRIORITY_MEDIUM),
            ],
            [
                'data' => t('Bets left', array(), array('context'=>'mespronos-block')),
            ],
            [
                'data' => t('Time left', array(), array('context'=>'mespronos-block')),
            ],
            [
                'data' => '',
            ]
        ];
    }

    public static function getFooter() {
        return [];
    }

    public static function parseDays($days, User $user, $page_league) {
        $rows = [];
        foreach ($days  as $day_id => $day) {
            $game_date = \DateTime::createFromFormat('Y-m-d\TH:i:s', $day->day_date, new \DateTimeZone("GMT"));
            $game_date->setTimezone(new \DateTimeZone("Europe/Paris"));
            $now_date = new \DateTime();

            $i = $game_date->diff($now_date);
            $bets_left = BetController::betsLeft($user, $day->entity);

            $day_renderable = $day->entity->getRenderableLabel();

            $row = [
              'data' => [
                'day' => [
                  'data' => render($day_renderable),
                  'class' => ['day-cell']
                ],
                'nb_game' => $day->nb_game,
                'bets_left' => $bets_left,
                'time_left' => $i->format('%a') > 0 ? t('@d days, @GH@im', array('@d'=>$i->format('%a'), '@G'=>$i->format('%H'), '@i'=>$i->format('%I'))) : t('@GH@im', array('@G'=>$i->format('%H'), '@i'=>$i->format('%I'))),
                'action' => '',
              ]
            ];

            $link_bet = Url::fromRoute('mespronos.day.bet', ['day'=>$day_id])->toString();

            $links = [
              'bets' => ['#markup'=>'<a class="picto" href="'.$link_bet.'" title="'.t('Bet now').'"><i class="fa fa-edit" aria-hidden="true"></i></a>'],
            ];

            $row['data']['action'] = ['data'=>render($links), 'class'=>'picto'];

            $rows[] = $row;
        }
        return $rows;
    }
}
