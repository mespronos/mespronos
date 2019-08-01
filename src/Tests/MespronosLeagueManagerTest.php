<?php

/**
 * @file
 * Contains Drupal\mespronos\Tests\MespronosLeagueTest.
 */

namespace Drupal\mespronos\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\mespronos\Entity\Sport;
use Drupal\mespronos\Entity\League;
use Drupal\mespronos\Entity\Team;
use Drupal\mespronos\Entity\Day;
use Drupal\mespronos\Entity\Game;
use Drupal\mespronos\Entity\Bet;

/**
 * Provides automated tests for the mespronos module.
 * @group mespronos
 */
class MespronosLeagueManagerTest extends WebTestBase {

  /** @var \Drupal\mespronos\Service\LeagueManager */
  protected $leagueManager;

  public $sport;
  public $league;
  public $team1;
  public $team2;
  public $day1;
  public $day2;
  public $game1;
  public $game2;
  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => 'MesPronos League Manager Service',
      'description' => 'Unit testing for League Manager Service.',
      'group' => 'MesPronos',
    ];
  }

  static public $modules = ['mespronos'];

  public function setUp() {
    parent::setUp();
    $this->leagueManager = \Drupal::service('mespronos.league_manager');

    $this->sport = Sport::create(['name' => 'Football']);
    $this->sport->save();

    $this->league = League::create([
      'sport' => $this->sport->id(),
      'name' => 'test championnat',
      'betting_type' => 'score',
      'classement' => TRUE,
      'status' => 'active',
    ]);
    $this->league->save();

    $this->team1 = Team::create(['name' => 'team1']);
    $this->team1->save();

    $this->team2 = Team::create(['name' => 'team2']);
    $this->team2->save();

  }

  public function testGetDaysNumber() {
    $this->assertEqual($this->leagueManager->getDaysNumber($this->league), 0, 'getDaysNumber, return 0 when there is no day in league');
    $i = 1;
    Day::create([
      'league' => $this->league->id(),
      'number' => $i,
      'name' => 'day test 1',
    ])->save();

    $this->assertEqual($this->leagueManager->getDaysNumber($this->league), 1, 'getDaysNumber, return 1 when adding a day');

    while ($i < 10) {
      $i++;
      Day::create([
        'league' => $this->league->id(),
        'number' => $i,
        'name' => 'day test ' . $i,
      ])->save();
    }
    $this->verbose($this->leagueManager->getDaysNumber($this->league));
    $this->assertEqual($this->leagueManager->getDaysNumber($this->league), $i, 'getDaysNumber, return the correct amout of days');
  }

}
