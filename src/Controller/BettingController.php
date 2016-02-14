<?php

/**
 * @file
 * Contains Drupal\mespronos\Controller\DefaultController.
 */

namespace Drupal\mespronos\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\mespronos\Entity\Controller\BetController;
use Drupal\mespronos\Entity\Controller\DayController;
use Drupal\mespronos\Entity\League;
use Drupal\mespronos\Entity\Day;
use Drupal\mespronos\Entity\Bet;
use Drupal\mespronos\Entity\Game;
use Drupal\mespronos\Entity\RankingDay;
use Drupal\user\Entity\User;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Class DefaultController.
 *
 * @package Drupal\mespronos\Controller
 */
class BettingController extends ControllerBase {

  public function nextBets($asBlock = false) {
    $user = \Drupal::currentUser();
    $user_uid =  $user->id();
    $days = DayController::getNextDaysToBet(10);
    $rows = [];
    $leagues = [];

    foreach ($days  as $day_id => $day) {
      $league_id = $day->entity->get('league')->first()->getValue()['target_id'];
      if(!isset($leagues[$league_id])) {
        $leagues[$league_id] = League::load($league_id);
      }
      $league = $leagues[$league_id];

      $game_date = \DateTime::createFromFormat('Y-m-d\TH:i:s',$day->day_date,new \DateTimeZone("GMT"));
      $game_date->setTimezone(new \DateTimeZone("Europe/Paris"));
      $now_date = new \DateTime();
      
      $i = $game_date->diff($now_date);
      $bets_left = BetController::betsLeft($user,$day->entity);

      $row = [
        $league->label(),
        $day->entity->label(),
        $day->nb_game,
        $bets_left,
        $i->format('%a') >0 ? $this->t('@d days, @GH@im',array('@d'=>$i->format('%a'),'@G'=>$i->format('%H'),'@i'=>$i->format('%i'))) : $this->t('@GH@im',array('@G'=>$i->format('%H'),'@i'=>$i->format('%i'))),
      ];
      if($user_uid>0) {
        if($bets_left > 0) {
          $row[] = Link::fromTextAndUrl(
            t('Bet !'),
            new Url('mespronos.day.bet', ['day' => $day_id],['query' => ['destination' => \Drupal::service('path.current')->getPath()]])
          );
        }
        else {
          $row[] = Link::fromTextAndUrl(
            t('Edit'),
            new Url('mespronos.day.bet', ['day' => $day_id],['query' => ['destination' => \Drupal::service('path.current')->getPath()]])
          );
        }
      }
      else {
        $row[] = Link::fromTextAndUrl(
          t('Log in and bet'),
          Url::fromRoute('mespronos.login',[],['query' => ['destination' => Url::fromRoute('mespronos.day.bet', ['day' => $day_id])->toString()]])
        );
      }
      $rows[] = $row;
    }
    if($asBlock) {
      $footer = [
        'data' => array(
          array(
            'data' => Link::fromTextAndUrl(
              $this->t('See all upcoming bets'),
              new Url('mespronos.nextbets')
            ),
            'colspan' => 7
          )
        )
      ];
    }
    else {
      $footer = [];
    }
    $header = [
      $this->t('League',array(),array('context'=>'mespronos')),
      $this->t('Day',array(),array('context'=>'mespronos')),
      $this->t('Games',array(),array('context'=>'mespronos')),
      $this->t('Bets left',array(),array('context'=>'mespronos')),
      $this->t('Time left',array(),array('context'=>'mespronos')),
      '',
    ];
    return [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
      '#footer' => $footer,
      '#cache' => [
        'contexts' => ['user'],
        'tags' => [ 'user:'.$user_uid,'nextbets'],
      ],
    ];
  }

  public function lastBets($forBlock = false) {
    $user = \Drupal::currentUser();
    $user_uid =  $user->id();
    $days = DayController::getlastDays(10);
    $rows = [];
    $leagues = [];

    foreach ($days  as $day_id => $day) {
      $league_id = $day->entity->get('league')->first()->getValue()['target_id'];
      if(!isset($leagues[$league_id])) {
        $leagues[$league_id] = League::load($league_id);
      }
      $league = $leagues[$league_id];

      $bets_done = BetController::betsDone($user,$day->entity);
      $points_won = BetController::PointsWon($user,$day->entity);
      if($user_uid>0) {
        $action_links = Link::fromTextAndUrl(
            t('Details'),
            Url::fromRoute('mespronos.lastbetsdetails',['day'=>$day->entity->id()])
        );
      }
      else {
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
        $league->label(),
        $day->entity->label(),
        $day->nb_game_over,
        $day->nb_game_with_score,
        $user_uid > 0 ? $bets_done : '/',
        $user_uid > 0 ? $points_won : '/',
        $action_links,
      ];
      $rows[] = $row;
    }
    $header = [
      $this->t('League',array(),array('context'=>'mespronos')),
      $this->t('Day',array(),array('context'=>'mespronos')),
      $this->t('Games over',array(),array('context'=>'mespronos')),
      $this->t('Games with score',array(),array('context'=>'mespronos')),
      $this->t('Bets done',array(),array('context'=>'mespronos')),
      $this->t('Points',array(),array('context'=>'mespronos')),
      ''

    ];
    if($forBlock) {
      $footer = [
        'data' => array(
          array(
            'data' => Link::fromTextAndUrl(
              $this->t('See all the last bets'),
              new Url('mespronos.lastbets')
            ),
            'colspan' => 7
          )
        )
      ];
    }
    else {
      $footer = [];
    }
    return [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
      '#footer' => $footer,
      '#cache' => [
        'contexts' => ['user'],
        'tags' => [ 'user:'.$user_uid,'lastbets'],
      ],
    ];
  }

  public function bet($day) {
    $user = \Drupal::currentUser();
    $day = Day::load($day);
    if($day === NULL) {
      drupal_set_message($this->t('This day doesn\'t exist.'),'error');
      throw new AccessDeniedHttpException();
    }
    $form = \Drupal::formBuilder()->getForm('Drupal\mespronos\Form\GamesBetting',$day,$user);
    return $form;
  }

  public function LastBetsForDay(Day $day, \Drupal\Core\Session\AccountProxyInterface $user = null) {
    if($user == null) {
      $user = \Drupal::currentUser();
    }
    else {
      $user = User::load($user);
    }
    $games = Game::getGamesForDay($day);
    $games_ids = $games['ids'];
    $games_entity = $games['entities'];
    $bets = Bet::getUserBetsForGames($games_ids,$user);
    $rows = [];
    foreach($games_entity as $gid => $game) {
      if($user->id() !== \Drupal::currentUser()->id() && !$game->isPassed()) {
        $bet = '?';
      }
      else {
        $bet = isset($bets[$gid]) ? $bets[$gid]->labelBet() : '/';
      }
      $points = isset($bets[$gid]) ? $bets[$gid]->get('points')->value : '/';
      $row = [
        $game->labelTeams(),
        $game->labelScore(),
        $bet,
        $points,
      ];
      $rows[] = $row;
    }

    $header = [
        $this->t('Game',array(),array('context'=>'mespronos')),
        $this->t('Score',array(),array('context'=>'mespronos')),
        $this->t('Bet',array(),array('context'=>'mespronos')),
        $this->t('Points',array(),array('context'=>'mespronos')),
    ];

    $table_array = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];
    $table = render($table_array);

    $tableRanking = RankingController::getRankingTableForDay($day);
    $tableRanking = render($tableRanking);
    return [
      '#markup'=>$table.$tableRanking,
      '#cache' => [
        'contexts' => ['user'],
        'tags' => [ 'user:'.$user->id(),'lastbets'],
      ],
    ];
  }
}
