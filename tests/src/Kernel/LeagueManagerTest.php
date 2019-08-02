<?php
namespace Drupal\Tests\mespronos\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\mespronos\Entity\Day;
use Drupal\mespronos\Entity\League;
use Drupal\mespronos\Entity\Sport;
use Drupal\mespronos\Entity\Team;

/**
 * Test mespronos league manager service
 *
 * @group mespronos
 */
class LeagueManagerTest extends MespronosKernelTestBase {

  public $sport;
  public $league;
  public $team1;
  public $team2;

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

  public function testIfTestAreWorking() {
    $this->assertEqual(1, 1, '1 is equal to 1');
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
