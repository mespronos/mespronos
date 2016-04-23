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

  public function testGlobal() {
    
  }

}
