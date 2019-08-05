<?php
namespace Drupal\Tests\mespronos\Kernel;

use Drupal\Component\Utility\Random;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\KernelTests\KernelTestBase;
use Drupal\mespronos\Entity\Day;
use Drupal\mespronos\Entity\Game;
use Drupal\mespronos\Entity\League;
use Drupal\mespronos\Entity\Sport;
use Drupal\mespronos\Entity\Team;

/**
 * Test mespronos league manager service
 *
 * @group mespronos
 */
abstract class MespronosKernelTestBase extends EntityKernelTestBase {

  public static $modules = ['user', 'mespronos', 'text', 'options', 'system', 'datetime'];
  /** @var Random */
  protected $randomGenerator;

  /** @var Day[] */
  protected $days = [];

  /** @var Sport  */
  protected $sport = NULL;

  /** @var League */
  protected $league = NULL;

  /** @var Game[]  */
  protected $games = [];

  /** @var Team[]  */
  protected $teams = [];

  /** @var \Drupal\user\Entity\User[]  */
  protected $users = [];

  public function setUp() {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installEntitySchema('sport');
    $this->installEntitySchema('league');
    $this->installEntitySchema('day');
    $this->installEntitySchema('team');
    $this->installEntitySchema('game');
    $this->installEntitySchema('bet');
    $this->randomGenerator = new Random();
  }

  public function createSport() {
    $this->sport = Sport::create(['name' => 'Football']);
    $this->sport->save();
  }

  public function createLeague() {
    $this->league = League::create([
      'sport' => $this->sport->id(),
      'name' => 'test championnat',
      'betting_type' => 'score',
      'classement' => TRUE,
      'status' => 'active',
    ]);
    $this->league->save();
  }

  public function createDays($number = 1) {
    while ($number > 0) {
      $day = Day::create(array(
        'league' => $this->league->id(),
        'number' => \count($this->days) + 1,
      ));
      $day->save();
      $this->days[] = $day;
      $number--;
    }
  }

  public function createTeams($number) {
    while ($number > 0) {
      $team = Team::create(['name' => $this->randomGenerator->string(8)]);
      $team->save();
      $this->teams[] = $team;
      $number--;
    }
  }

  public function setUpGame($id, $team1, $team2, $day, $date) {
    $this->games[$id] = Game::create(array(
      'team_1' => $team1->id(),
      'team_2' => $team2->id,
      'day' => $day->id(),
      'game_date' => $date,
    ));
    $this->games[$id]->save();
    return $this->games[$id];
  }

}
