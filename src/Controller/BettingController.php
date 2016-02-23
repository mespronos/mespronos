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
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\user\Entity\User;

/**
 * Class DefaultController.
 *
 * @package Drupal\mespronos\Controller
 */
class BettingController extends ControllerBase {

  public function nextBets(League $league = null,$nb=10) {
    $user = \Drupal::currentUser();
    $user_uid =  $user->id();
    $days = DayController::getNextDaysToBet($nb,$league);
    $page_league = isset($league);
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
        'data' => [
          'day' => $league->label().'<br />'.$day->entity->label(),
          'nb_game' => $day->nb_game,
          'bets_left' => $bets_left,
          'time_left' => $i->format('%a') >0 ? $this->t('@d days, @GH@im',array('@d'=>$i->format('%a'),'@G'=>$i->format('%H'),'@i'=>$i->format('%i'))) : $this->t('@GH@im',array('@G'=>$i->format('%H'),'@i'=>$i->format('%i'))),
          'action' => '',
        ]
      ];
      $cell = [];
      if(!$page_league) {
        $cell['link'] = Link::fromTextAndUrl($league->label(),Url::fromRoute('mespronos.league.index',['league'=>$league->id()]))->toRenderable();
        $cell['backtoline'] = ['#markup' => '<br />'];
      }

      $cell['day'] = [
        '#type' => 'markup',
        '#markup' => $day->entity->label(),
      ];
      
      $row['data']['day'] = render($cell);

      if($user_uid>0) {
        if($bets_left > 0) {
          $text = t('Bet');
        }
        else {
          $text = t('Edit');
        }
        $row['data']['action'] = Link::fromTextAndUrl(
          $text,
          new Url('mespronos.day.bet', ['day' => $day_id],['query' => ['destination' => \Drupal::service('path.current')->getPath()]])
        );
      }
      else {
        $row['data']['action'] = Link::fromTextAndUrl(
          t('Log in and bet'),
          Url::fromRoute('mespronos.login',[],['query' => ['destination' => Url::fromRoute('mespronos.day.bet', ['day' => $day_id])->toString()]])
        );
      }
      $rows[] = $row;
    }
    $footer = [];
    $header = [
      $this->t('Day',array(),array('context'=>'mespronos-block')),
      $this->t('Games',array(),array('context'=>'mespronos-block')),
      $this->t('Bets left',array(),array('context'=>'mespronos-block')),
      $this->t('Time left',array(),array('context'=>'mespronos-block')),
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
        'max-age' => '120',
      ],
    ];
  }

  public function bet(Day $day) {
    $user = \Drupal::currentUser();
    if($day === NULL) {
      drupal_set_message($this->t('This day doesn\'t exist.'),'error');
      throw new AccessDeniedHttpException();
    }
    $form = \Drupal::formBuilder()->getForm('Drupal\mespronos\Form\GamesBetting',$day,$user);
    return $form;
  }

  public function betTitle(Day $day) {
    $league = $day->getLeague();
    return t('Bet on @day',array('@day'=>$league->label().' - '.$day->label()));
  }
  
}
