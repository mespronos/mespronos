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
use Drupal\mespronos\Entity\Sport;
use Drupal\mespronos\Entity\League;
use Drupal\mespronos\Entity\Team;
use Drupal\mespronos\Entity\Game;
use Drupal\mespronos\Entity\Bet;


/**
 * Provides automated tests for the mespronos module.
 * @group mespronos
 */
class MespronosReminderEntityTest extends WebTestBase {
  public $sport;
  public $league;
  public $day;
  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => "MesPronos Reminder Entity functionality",
      'description' => 'Test Unit for reminder Entity features.',
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

    $this->day = Day::create(array(
      'league' => $this->league->id(),
      'number' => 1,
    ));
    $this->day->save();

  }

  public function testSimpleCreation() {
    $reminder = Reminder::create(array(
      'day' => $this->day->id(),
    ));

    $this->assertTrue($reminder->save(),t('Reminder saving return true'));
  }

  public function testLoadForDay() {
    $this->assertFalse(Reminder::loadForDay($this->day->id()),t('Reminder static method return false when there is no reminder for day'));
    $reminder = Reminder::create(array(
      'day' => $this->day->id(),
    ));
    $reminder->save();
    $rem = Reminder::loadForDay($this->day->id());
    $this->assertEqual($rem->id(),$reminder->id(),t('Reminder static method return reminder entity when there is one for a given day'));
  }

}
