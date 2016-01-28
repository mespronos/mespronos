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
class MespronosBetPointsTest extends WebTestBase {
  public $sport;
  public $league;
  public $team1;
  public $team2;
  public $day;
  public $game;
  public $better1;
  public $better2;
  public $better3;
  public $better4;
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

    $this->team1 = Team::create(['name' => 'team1']);
    $this->team1->save();

    $this->team2 = Team::create(['name' => 'team1']);
    $this->team2->save();

    $this->team2->save();

    $this->day = Day::create(array(
      'league' => $this->league->id(),
      'number' => 1,
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

    $this->better1 = $this->drupalCreateUser();
    $this->better2 = $this->drupalCreateUser();
    $this->better3 = $this->drupalCreateUser();
    $this->better4 = $this->drupalCreateUser();
  }

  public function testBettingsPointsOnDraw() {

    $betTooGood = Bet::create(array(
      'better' => $this->better1->id(),
      'game' => $this->game->id(),
      'score_team_1' => 1,
      'score_team_2' => 1,
    ));
    $betTooGood->save();

    $betABitGood = Bet::create(array(
      'better' => $this->better2->id(),
      'game' => $this->game->id(),
      'score_team_1' => 2,
      'score_team_2' => 2,
    ));
    $betABitGood->save();

    $betswrong1 = Bet::create(array(
      'better' => $this->better3->id(),
      'game' => $this->game->id(),
      'score_team_1' => 2,
      'score_team_2' => 1,
    ));
    $betswrong1->save();

    $betswrong2 = Bet::create(array(
      'better' => $this->better3->id(),
      'game' => $this->game->id(),
      'score_team_1' => 1,
      'score_team_2' => 2,
    ));
    $betswrong2->save();

    $this->game->setScore(1,1);
    $this->game->save();

    $betTooGood = Bet::load($betTooGood->id());
    $betABitGood = Bet::load($betABitGood->id());
    $betswrong1 = Bet::load($betswrong1->id());
    $betswrong2 = Bet::load($betswrong2->id());

    $this->assertTrue($this->game->isScoreSetted(),t('Game score is setted'));

    $this->assertEqual($betTooGood->getPoints(),10,'On a draw, perfect bet worth 10 points');
    $this->assertEqual($betABitGood->getPoints(),5,'On a draw, betting a draw worth 5 points');
    $this->assertEqual($betswrong1->getPoints(),1,'On a draw, betting on team 1 worth 1 points');
    $this->assertEqual($betswrong2->getPoints(),1,'On a draw, betting on team 2 worth 1 points');


  }


  public function testBettingsPointsOnTeam1Winning() {

    $betTooGood = Bet::create(array(
      'better' => $this->better1->id(),
      'game' => $this->game->id(),
      'score_team_1' => 2,
      'score_team_2' => 1,
    ));
    $betTooGood->save();

    $betABitGood = Bet::create(array(
      'better' => $this->better2->id(),
      'game' => $this->game->id(),
      'score_team_1' => 2,
      'score_team_2' => 0,
    ));
    $betABitGood->save();

    $betswrong1 = Bet::create(array(
      'better' => $this->better3->id(),
      'game' => $this->game->id(),
      'score_team_1' => 1,
      'score_team_2' => 1,
    ));
    $betswrong1->save();

    $betswrong2 = Bet::create(array(
      'better' => $this->better3->id(),
      'game' => $this->game->id(),
      'score_team_1' => 1,
      'score_team_2' => 2,
    ));
    $betswrong2->save();

    $this->game->setScore(2,1);
    $this->game->save();

    $betTooGood = Bet::load($betTooGood->id());
    $betABitGood = Bet::load($betABitGood->id());
    $betswrong1 = Bet::load($betswrong1->id());
    $betswrong2 = Bet::load($betswrong2->id());

    $this->assertTrue($this->game->isScoreSetted(),t('Game score is setted'));

    $this->assertEqual($betTooGood->getPoints(),10,'On a team1 winning, perfect bet worth 10 points');
    $this->assertEqual($betABitGood->getPoints(),5,'On a team1 winning, betting on team1 worth 5 points');
    $this->assertEqual($betswrong1->getPoints(),1,'On a team1 winning, betting on draw worth 1 points');
    $this->assertEqual($betswrong2->getPoints(),1,'On a team1 winning, betting on team 2 worth 1 points');
  }


  public function testBettingsPointsOnTeam2Winning() {

    $betTooGood = Bet::create(array(
      'better' => $this->better1->id(),
      'game' => $this->game->id(),
      'score_team_1' => 0,
      'score_team_2' => 2,
    ));
    $betTooGood->save();

    $betABitGood = Bet::create(array(
      'better' => $this->better2->id(),
      'game' => $this->game->id(),
      'score_team_1' => 1,
      'score_team_2' => 2,
    ));
    $betABitGood->save();

    $betswrong1 = Bet::create(array(
      'better' => $this->better3->id(),
      'game' => $this->game->id(),
      'score_team_1' => 1,
      'score_team_2' => 1,
    ));
    $betswrong1->save();

    $betswrong2 = Bet::create(array(
      'better' => $this->better3->id(),
      'game' => $this->game->id(),
      'score_team_1' => 2,
      'score_team_2' => 0,
    ));
    $betswrong2->save();

    $this->game->setScore(0,2);
    $this->game->save();

    $betTooGood = Bet::load($betTooGood->id());
    $betABitGood = Bet::load($betABitGood->id());
    $betswrong1 = Bet::load($betswrong1->id());
    $betswrong2 = Bet::load($betswrong2->id());

    $this->assertTrue($this->game->isScoreSetted(),t('Game score is setted'));

    $this->assertEqual($betTooGood->getPoints(),10,'On a team2 winning, perfect bet worth 10 points');
    $this->assertEqual($betABitGood->getPoints(),5,'On a team2 winning, betting on team2 worth 5 points');
    $this->assertEqual($betswrong1->getPoints(),1,'On a team2 winning, betting on draw worth 1 points');
    $this->assertEqual($betswrong2->getPoints(),1,'On a team2 winning, betting on team 1 worth 1 points');
  }
}
