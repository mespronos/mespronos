<?php

/**
 * @file
 * Contains Drupal\mespronos\Controller\DefaultController.
 */

namespace Drupal\mespronos\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\mespronos\Entity\Controller\BetController;
use Drupal\mespronos\Entity\Controller\DayController;
use Drupal\mespronos\Entity\Controller\UserInvolveController;
use Drupal\mespronos\Entity\League;
use Drupal\mespronos\Entity\Day;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Class DefaultController.
 *
 * @package Drupal\mespronos\Controller
 */
class BettingController extends ControllerBase {
  /**
   * Hello.
   *
   * @return string
   *   Return Hello string.
   */
  public function index() {
    return [
        '#type' => 'markup',
        '#markup' => $this->t('Hello World!', [])
    ];
  }

  public function nextBets() {
    $user = \Drupal::currentUser();
    $user_uid =  $user->id();
    $days = DayController::getNextDaysToBet(10);

    foreach ($days  as $day_id => $day) {
      $league_id = $day->entity->get('league')->first()->getValue()['target_id'];
      if(!isset($leagues[$league_id])) {
        $leagues[$league_id] = League::load($league_id);
      }
      $league = $leagues[$league_id];
      if(!isset($user_involvements[$league_id])) {
        $user_involvements[$league_id] = UserInvolveController::isUserInvolve($user_uid ,$league_id);
      }
      $day->involve = $user_involvements[$league_id];

      $game_date = \DateTime::createFromFormat('Y-m-d\TH:i:s',$day->day_date,new \DateTimeZone("GMT"));
      $game_date->setTimezone(new \DateTimeZone("Europe/Paris"));
      $now_date = new \DateTime();
      
      $i = $game_date->diff($now_date);
      $action_links = self::getActionBetLink($day->entity,$league,$user_uid,$user_involvements[$league_id]);
      $bets_done = BetController::betsDone($user,$day->entity);
      $row = [
        $league->label(),
        $day->entity->label(),
        $day->nb_game,
        $day->nb_game - $bets_done,

        $i->format('%a') >0 ? $this->t('@d days, @GH@im',array('@d'=>$i->format('%a'),'@G'=>$i->format('%H'),'@i'=>$i->format('%i'))) : $this->t('@GH@im',array('@G'=>$i->format('%H'),'@i'=>$i->format('%i'))),
        $action_links,
      ];
      $rows[] = $row;
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
    ];
  }

  public function lastBets() {
    $user = \Drupal::currentUser();
    $user_uid =  $user->id();
    $days = DayController::getlastDays(10);

    foreach ($days  as $day_id => $day) {
      $league_id = $day->entity->get('league')->first()->getValue()['target_id'];
      if(!isset($leagues[$league_id])) {
        $leagues[$league_id] = League::load($league_id);
      }
      $league = $leagues[$league_id];
      if(!isset($user_involvements[$league_id])) {
        $user_involvements[$league_id] = UserInvolveController::isUserInvolve($user_uid ,$league_id);
      }

      $game_date = \DateTime::createFromFormat('Y-m-d\TH:i:s',$day->day_date);
      $now_date = new \DateTime();

      $i = $game_date->diff($now_date);

      $bets_done = BetController::betsDone($user,$day->entity);
      $points_won = BetController::PointsWon($user,$day->entity);
      $row = [
        $league->label(),
        $day->entity->label(),
        $day->nb_game,
        $bets_done,
        $points_won,

      ];
      $rows[] = $row;
    }
    $header = [
      $this->t('League',array(),array('context'=>'mespronos')),
      $this->t('Day',array(),array('context'=>'mespronos')),
      $this->t('Games',array(),array('context'=>'mespronos')),
      $this->t('Bets done',array(),array('context'=>'mespronos')),
      $this->t('Points',array(),array('context'=>'mespronos')),

    ];
    return [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];
  }

  public function bet($day) {
    $user = \Drupal::currentUser();
    $user_uid =  $user->id();
    $day_storage = \Drupal::entityManager()->getStorage('day');
    $day = $day_storage->load($day);
    if($day === NULL) {
      drupal_set_message($this->t('This day doesn\'t exist.'),'error');
      throw new AccessDeniedHttpException();
    }
    $league_id =$day->get('league')->first()->getValue()['target_id'];
    if(!UserInvolveController::isUserInvolve($user_uid,$league_id)) {
      drupal_set_message($this->t('You\'re not subscribed to this day'),'warning');
      throw new AccessDeniedHttpException();
    }

    $form = \Drupal::formBuilder()->getForm('Drupal\mespronos\Form\GamesBetting',$day,$user);
    return $form;

  }

  public static function getActionBetLink(Day $day,League $league,$user_uid,$isInvolve) {
    if($isInvolve) {
      $action_links = Link::fromTextAndUrl(
        t('Bet now'),
        new Url('mespronos.day.bet', array('day' => $day->id()))
      );
    }
    else {
      if($user_uid == 0) {
        if(\Drupal::moduleHandler()->moduleExists(('mespronos_registration'))) {
          $action_links = Link::fromTextAndUrl(
            t('Register or login and start betting'),
            Url::fromRoute('mespronos_registration.join',[],[
                'query' => [
                  'destination' => Url::fromRoute('mespronos.league.register', ['league' => $league->id()])->toString(),
                ]
              ]
            )
          );
        }
        else {
          $action_links = Link::fromTextAndUrl(
            t('Register or login and start betting'),
            Url::fromRoute('user.register',[],[
                'query' => [
                  'destination' => Url::fromRoute('mespronos.league.register', ['league' => $league->id()])->toString(),
                ]
              ]
            )
          );
        }
      }
      else {
        $action_links = Link::fromTextAndUrl(
          t('Start betting now !'),
          new Url('mespronos.league.register', array('league' => $league->id()))
        );
      }
    }
    return $action_links;
  }
}
