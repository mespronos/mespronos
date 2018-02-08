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
class MespronosSingleBetTest extends WebTestBase {
  public $sport;
  public $league;
  public $team1;
  public $team2;
  public $day;
  public $game;
  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => "MesPronos RankingDay functionality",
      'description' => 'Test Unit for user permissions.',
      'group' => 'MesPronos',
    );
  }

  static public $modules = array(
    'mespronos',
  );

  public function setUp() {
    parent::setUp();
    $this->sport = Sport::create(['name' => 'Football',]);
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

    $this->team2   = Team::create(['name' => 'team2']);
    $this->team2->save();

    $this->day = Day::create([
      'league' => $this->league->id(),
      'number' => 1,
      'name' => 'day test',
    ]);
    $this->day->save();

    $dateO = new \DateTime();
    $date = $dateO->format('Y-m-d\TH:i:s');

    $this->game = Game::create(array(
      'team_1' => $this->team1->id(),
      'team_2' => $this->team2->id(),
      'day' => $this->day->id(),
      'game_date' => $date,
    ));
    $this->game->save();


  }

  public function testBetPoints() {
    $bet = Bet::create([
      'better' => 1,
      'game' => $this->game->id(),
      'score_team_1' => 1,
      'score_team_2' => 1,
    ]);

    $bet->save();

    $this->game->setScore(1, 1);
    $this->game->save();
    $this->setBatch();
    $bet_reloaded = Bet::load($bet->id());

    $points = $this->league->getPoints();

    $this->assertEqual($bet_reloaded->getPoints(), $points['points_score_found'], t('Setting a game score update bets points'));
  }

}
