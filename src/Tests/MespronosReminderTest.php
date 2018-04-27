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
  public $day2;
  public $game1;
  public $game2;
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

    $this->day2 = Day::create(array(
      'league' => $this->league->id(),
      'number' => 2,
    ));
    $this->day2->save();

    $this->better1 = $this->drupalCreateUser();
    $this->better2 = $this->drupalCreateUser();

  }


  public function setUpGame($id,$team1,$team2,$day,$date) {
    $this->game{$id} = Game::create(array(
      'team_1' => $team1->id(),
      'team_2' => $team2->id,
      'day' => $day->id(),
      'game_date' => $date,
    ));
    $this->game{$id}->save();
    return $this->game{$id};
  }

  public function testReminderInitReturnTrue() {
    $this->assertFalse(ReminderController::init());
    \Drupal::configFactory()->getEditable('mespronos.reminder')->set('hours',36)->save();
    \Drupal::configFactory()->getEditable('mespronos.reminder')->set('enabled',TRUE)->save();
    $this->assertTrue(ReminderController::init());
  }

  public function testDayGetUpcommingMethodWithUpcomingGame() {
    $hours = 10;
    $upcomming = ReminderController::getUpcomming($hours);
    $this->assertTrue(is_array($upcomming),t('The method is returning an array'));
    $this->assertEqual(count($upcomming),0,t('The returned array of games is empty when no game is set'));
    $dateO = new \DateTime(null,new \DateTimeZone("UTC"));
    $dateO->add(new \DateInterval('PT5H'));
    $date = $dateO->format('Y-m-d\TH:i:s');

    $this->setUpGame(1,$this->team1,$this->team2,$this->day1,$date);

    $upcomming = ReminderController::getUpcomming($hours);
    $this->assertEqual(count($upcomming),1,t('The returned array contain one game when a game is set'));
  }

  public function testDayGetUpcommingMethodWithUpcomingGameButUnderNbHours() {
    $hours = 5;
    $dateO = new \DateTime(null,new \DateTimeZone("UTC"));
    $dateO->add(new \DateInterval('PT10H'));
    $date = $dateO->format('Y-m-d\TH:i:s');

    $this->setUpGame(1,$this->team1,$this->team2,$this->day1,$date);

    $upcomming = ReminderController::getUpcomming($hours);
    $this->assertTrue(is_array($upcomming),t('The method is returning an array'));
    $this->assertEqual(count($upcomming),0,t('The returned array is empty when the game is set later'));
  }

  public function testDayGetUpcommingMethodWithUpcomingGames() {
    $hours = 10;
    $dateO = new \DateTime(null,new \DateTimeZone("UTC"));
    $dateO->add(new \DateInterval('PT5H'));
    $date = $dateO->format('Y-m-d\TH:i:s');

    $this->setUpGame(1,$this->team1,$this->team2,$this->day1,$date);

    $this->setUpGame(2,$this->team1,$this->team2,$this->day1,$date);

    $upcomming = ReminderController::getUpcomming($hours);
    $this->assertEqual(count($upcomming),2,t('The returned array contains two games when two games exists'));
  }

  public function testDayGetUpcommingMethodWithUpcomingGamesFromTwoDaysWithOnlyOneUpcommingGame() {
    $hours = 10;
    $dateO = new \DateTime(null,new \DateTimeZone("UTC"));
    $dateO->add(new \DateInterval('PT5H'));
    $date = $dateO->format('Y-m-d\TH:i:s');

    $this->setUpGame(1,$this->team1,$this->team2,$this->day1,$date);

    $dateO->add(new \DateInterval('PT10H'));
    $date = $dateO->format('Y-m-d\TH:i:s');

    $this->setUpGame(1,$this->team1,$this->team2,$this->day2,$date);

    $upcomming = ReminderController::getUpcomming($hours);
    $this->assertEqual(count($upcomming),1,t('The returned array contains one days when there is only one game'));
  }

  public function testGetUserWithEnabledReminder() {
    $user_ids = ReminderController::getUserWithEnabledReminder();
    $this->assertTrue(is_array($user_ids),t('The method is returning an array'));
    $this->assertEqual(count($user_ids),2,t('By default user has reminder activated'));
  }

  public function testGetUserWithEnabledReminderWithOneEnabledUser() {
    $this->better1->set("field_reminder_enable", 0);
    $this->better1->save();
    $user_ids = ReminderController::getUserWithEnabledReminder();
    $this->assertEqual(count($user_ids),1,t('The returned array contains one element'));
  }

  public function testDoUserHasMissingBets() {
    $hours = 10;
    $dateO = new \DateTime(null,new \DateTimeZone("UTC"));
    $dateO->add(new \DateInterval('PT5H'));
    $date = $dateO->format('Y-m-d\TH:i:s');

    $game = $this->setUpGame(1,$this->team1,$this->team2,$this->day2,$date);

    $upcomming = ReminderController::getUpcomming($hours);

    $this->assertEqual(count($upcomming),1,t('The returned array contains one game when there is only one game'));
    $this->assertTrue(is_bool(ReminderController::doUserHasMissingBets($this->better1->id(),$upcomming)),t('The static function ReminderController::doUserHasMissingBets return a boolean'));
    $this->assertTrue(ReminderController::doUserHasMissingBets($this->better1->id(),$upcomming),t('The static function ReminderController::doUserHasMissingBets return true when there is a bet to be done'));

    $bet = Bet::create(array(
      'better' => $this->better1->id(),
      'game' => $game->id(),
      'score_team_1' => 1,
      'score_team_2' => 1,
      'points' => 10,
    ));

    $bet->save();

    $this->assertFalse(ReminderController::doUserHasMissingBets($this->better1->id(),$upcomming),t('The static function ReminderController::doUserHasMissingBets return False when there is no bet to be done'));
  }


}
