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
class MespronosBetTest extends WebTestBase {
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
    $this->sport = Sport::create(array(
      'name' => 'Football',
    ));
    $this->sport->save();

    $this->league = League::create(array(
      'sport' => $this->sport->id(),
      'name' => 'test championnat',
      'betting_type' => 'score',
      'classement' => true,
      'status' => 'active',
    ));
    $this->league->save();

    $this->team1 = Team::create(array(
      'name' => 'team1',
    ));
    $this->team1->save();

    $this->team2   = Team::create(array(
      'name' => 'team2',
    ));
    $this->team2->save();

    $this->day = Day::create(array(
      'league' => $this->league->id(),
      'number' => 1,
      'name' => 'day test',
    ));
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

  public function testCreationBet() {
    $bet = Bet::create(array(
      'better' => 1,
      'game' => $this->game->id(),
      'score_team_1' => 1,
      'score_team_2' => 1,
      'points' => 10,
    ));

    $this->assertTrue($bet->save(),t('Bet saving return true'));
  }


}
