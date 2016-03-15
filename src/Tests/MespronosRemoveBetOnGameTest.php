<?php

/**
 * @file
 * Contains Drupal\mespronos\Tests\MespronosRemoveBetOnGameTest.
 */

namespace Drupal\mespronos\Tests;

use Drupal\mespronos\Entity\RankingDay;
use Drupal\mespronos\Controller\RankingController;
use Drupal\simpletest\WebTestBase;
use Drupal\mespronos\Entity\Sport;
use Drupal\mespronos\Entity\League;
use Drupal\mespronos\Entity\Team;
use Drupal\mespronos\Entity\Day;
use Drupal\mespronos\Entity\Game;
use Drupal\mespronos\Entity\Bet;
use MyProject\Proxies\__CG__\OtherProject\Proxies\__CG__\stdClass;

/**
 * Provides automated tests for the mespronos module.
 * @group mespronos
 */
class MespronosRemoveBetOnGameTest extends WebTestBase {
  public $sport;
  public $league;
  public $day1;
  public $day2;
  public $team1;
  public $team2;
  public $team3;
  public $team4;
  public $game1;
  public $game2;
  public $game3;
  public $game4;
  public $better1;
  public $better2;
  public $better3;
  public $better4;
  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => "MesPronos Bet removing functionality",
      'description' => 'Test Unit on bet removing on days.',
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

    $this->team1 = Team::create(['name' => 'team1']);
    $this->team1->save();
    $this->team2 = Team::create(['name' => 'team2']);
    $this->team2->save();
    $this->team3 = Team::create(['name' => 'team3']);
    $this->team3->save();
    $this->team4 = Team::create(['name' => 'team4']);
    $this->team4->save();

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
    $this->better3 = $this->drupalCreateUser();
    $this->better4 = $this->drupalCreateUser();

    $dateO = new \DateTime();
    $date = $dateO->format('Y-m-d\TH:i:s');

    $this->game1 = Game::create(array(
      'team_1' => $this->team1->id(),
      'team_2' => $this->team2->id(),
      'day' => $this->day1->id(),
      'game_date' => $date,
    ));
    $this->game1->save();

    $this->game2 = Game::create(array(
      'team_1' => $this->team3->id(),
      'team_2' => $this->team4->id(),
      'day' => $this->day1->id(),
      'game_date' => $date,
    ));
    $this->game2->save();

    $this->game3 = Game::create(array(
      'team_1' => $this->team1->id(),
      'team_2' => $this->team2->id(),
      'day' => $this->day2->id(),
      'game_date' => $date,
    ));
    $this->game3->save();

    $this->game4 = Game::create(array(
      'team_1' => $this->team3->id(),
      'team_2' => $this->team4->id(),
      'day' => $this->day2->id(),
      'game_date' => $date,
    ));
    $this->game4->save();
  }

  public function testSimpleRemoving() {
    $this->assertEqual($this->game1->removeBets(),0,t('the method Game::removeBets return 0 when there\`s no bet to remove'));

    $bet = Bet::create(array(
      'better' => $this->better1->id(),
      'game' => $this->game1->id(),
      'score_team_1' => 1,
      'score_team_2' => 1,
    ));
    $bet->save();

    $this->assertEqual($this->game1->removeBets(),1,t('the method Game::removeBets return the number of bets removed'));
    $this->assertEqual($this->game1->removeBets(),0,t('the method Game::removeBets return 0 when bets has been removed'));

  }
}
