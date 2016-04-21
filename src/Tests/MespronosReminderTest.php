<?php

/**
 * @file
 * Contains Drupal\mespronos\Tests\MespronosReminderTest.
 */

namespace Drupal\mespronos\Tests;

use Drupal\mespronos\Controller\ReminderController;
use Drupal\simpletest\WebTestBase;
use Drupal\mespronos\Entity\Reminder;
use Drupal\mespronos\Entity\Day;
use Drupal\mespronos\Controller\DayController;
use Drupal\mespronos\Entity\Sport;
use Drupal\mespronos\Entity\League;
use Drupal\mespronos\Entity\Team;
use Drupal\mespronos\Entity\Game;
use Drupal\mespronos\Entity\Bet;


/**
 * Provides automated tests for the mespronos module.
 * @group mespronos
 */
class MespronosReminderTest extends WebTestBase {
  public $sport;
  public $league;
  public $team1;
  public $team2;
  public $day1;
  public $game;
  public $better1;
  public $better2;
  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => "MesPronos Reminder functionality",
      'description' => 'Test Unit for reminder features.',
      'group' => 'MesPronos',
    );
  }

  static public $modules = array(
    'mespronos',
  );
  
  public function setUp() {
    parent::setUp();

    $this->sport = Sport::create(['name' =>'Football']);
    $this->sport->save();

    $this->league = League::create(array(
      'sport' => $this->sport->id(),
      'name' => 'test championnat',
      'betting_type' => 'score',
      'classement' => true,
      'status' => 'active',
    ));
    $this->league->save();

    $this->team1 = Team::create(['name' => 'team1']);
    $this->team1->save();
    $this->team2 = Team::create(['name' => 'team2']);
    $this->team2->save();

    $this->day1 = Day::create(array(
      'league' => $this->league->id(),
      'number' => 1,
    ));
    $this->day1->save();

    $this->better1 = $this->drupalCreateUser();
    $this->better2 = $this->drupalCreateUser();

  }

  public function testReminderInitReturnTrue() {
    $this->assertFalse(ReminderController::init());
    $this->assertTrue(is_array(ReminderController::getHoursDefined()));
    $this->assertEqual(count(ReminderController::getHoursDefined()),0);

    \Drupal::configFactory()->getEditable('mespronos.reminder')->set('hours',36)->save();

    \Drupal::configFactory()->getEditable('mespronos.reminder')->set('enabled',TRUE)->save();

    $this->assertTrue(ReminderController::init());
  }

  public function testDayGetUpcommingMethodWithUpcomingGame() {
    $hours = 10;
    $days = ReminderController::getUpcomming($hours);
    $this->assertTrue(is_array($days),t('The method is returning an array'));
    $this->assertEqual(count($days),0,t('The returned array is empty when no game is set'));

    $dateO = new \DateTime(null,new \DateTimeZone("UTC"));
    $dateO->add(new \DateInterval('PT5H'));
    $date = $dateO->format('Y-m-d\TH:i:s');
    $this->game = Game::create(array(
      'team_1' => $this->team1->id(),
      'team_2' => $this->team2->id(),
      'day' => $this->day1->id(),
      'game_date' => $date,
    ));
    $this->game->save();

    $days = ReminderController::getUpcomming($hours);
    $this->assertEqual(count($days),1,t('The returned array contain one day when a game is set'));
  }

  public function testDayGetUpcommingMethodWithUpcomingGameButUnderNbHours() {
    $hours = 5;
    $dateO = new \DateTime(null,new \DateTimeZone("UTC"));
    $dateO->add(new \DateInterval('PT10H'));
    $date = $dateO->format('Y-m-d\TH:i:s');
    $this->game = Game::create(array(
      'team_1' => $this->team1->id(),
      'team_2' => $this->team2->id(),
      'day' => $this->day1->id(),
      'game_date' => $date,
    ));
    $this->game->save();

    $days = ReminderController::getUpcomming($hours);
    $this->assertTrue(is_array($days),t('The method is returning an array'));
    $this->assertEqual(count($days),0,t('The returned array is empty when the game is set later'));
  }

}
