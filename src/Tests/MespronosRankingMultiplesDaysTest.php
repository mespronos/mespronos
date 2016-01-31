<?php

/**
 * @file
 * Contains Drupal\mespronos\Tests\MespronosLeagueTest.
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
class MespronosRankingMultiplesDaysTest extends WebTestBase {
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

  public function testSimpleWithOnlyOneDay() {
    $bets = [];
    $bets[] = Bet::create(array(
      'better' => $this->better1->id(),
      'game' => $this->game1->id(),
      'score_team_1' => 1,
      'score_team_2' => 1,
    ));
    $bets[] = Bet::create(array(
      'better' => $this->better2->id(),
      'game' => $this->game1->id(),
      'score_team_1' => 1,
      'score_team_2' => 1,
    ));
    $bets[] = Bet::create(array(
      'better' => $this->better3->id(),
      'game' => $this->game1->id(),
      'score_team_1' => 1,
      'score_team_2' => 1,
    ));
    $bets[] = Bet::create(array(
      'better' => $this->better4->id(),
      'game' => $this->game1->id(),
      'score_team_1' => 1,
      'score_team_2' => 1,
    ));
    //GAME 3 (day 2)
    $bets[] = Bet::create(array(
      'better' => $this->better1->id(),
      'game' => $this->game3->id(),
      'score_team_1' => 1,
      'score_team_2' => 1,
    ));
    $bets[] = Bet::create(array(
      'better' => $this->better2->id(),
      'game' => $this->game3->id(),
      'score_team_1' => 1,
      'score_team_2' => 1,
    ));

    foreach($bets as $bet) {
      $bet->save();
    }

    $this->game1->setScore(1,1)->save();
    $this->game3->setScore(1,1)->save();

    $this->assertTrue($this->game1->isScoreSetted(),t('Game1 score is setted'));
    $this->assertTrue($this->game3->isScoreSetted(),t('Game2 score is setted'));

    foreach($bets as $bet) {
      $bet = Bet::load($bet->id());
      $this->assertEqual($bet->getPoints(),10,t('good bets worth 10 points'));
    }

    RankingDay::createRanking($this->day1);
    RankingDay::createRanking($this->day2);

    $ranking_day_1 = RankingDay::getRankingForDay($this->day1);
    $ranking_day_2 = RankingDay::getRankingForDay($this->day2);

    $this->assertEqual(count($ranking_day_1),4,t('Day 1 : four betters, so ranking contains 4 lines'));
    $this->assertEqual(count($ranking_day_2),2,t('Day 2 : two betters, so ranking contains 2 lines'));
  }

  public function testRankingOnSeveralDays() {
    $bets = [];
    $bets[1] = Bet::create(array(
      'better' => $this->better1->id(),
      'game' => $this->game1->id(),
      'score_team_1' => 1,
      'score_team_2' => 1,
    ));
    $bets[2] = Bet::create(array(
      'better' => $this->better2->id(),
      'game' => $this->game1->id(),
      'score_team_1' => 1,
      'score_team_2' => 1,
    ));
    $bets[3] = Bet::create(array(
      'better' => $this->better3->id(),
      'game' => $this->game1->id(),
      'score_team_1' => 2,
      'score_team_2' => 2,
    ));
    $bets[4] = Bet::create(array(
      'better' => $this->better4->id(),
      'game' => $this->game1->id(),
      'score_team_1' => 1,
      'score_team_2' => 0,
    ));

    foreach($bets as $bet) {
      $bet->save();
    }

    //better 1 => 10points
    $points[1] = 10;
    //better 2 => 10points
    $points[2] = 10;
    //better 3 => 5points
    $points[3] = 5;
    //better 4 => 1points
    $points[4] = 1;

    $this->game1->setScore(1,1)->save();

    foreach($bets as $key => $bet) {
      $bet = Bet::load($bet->id());
      $this->assertEqual($bet->getPoints(),$points[$key],t('Bet @id worth @points',array('@id'=>$key,'@points'=>$points[$key])));
    }

    RankingDay::createRanking($this->day1);
    $ranking_day_1 = RankingDay::getRankingForDay($this->day1);

    $this->assertEqual(count($ranking_day_1),4,t('Day 1 : four betters, so ranking contains 4 lines'));

    $ranking_1 = array_shift($ranking_day_1);
    $ranking_2 = array_shift($ranking_day_1);
    $ranking_3 = array_shift($ranking_day_1);
    $ranking_4 = array_shift($ranking_day_1);

    $this->assertEqual($ranking_1->getPoints(),10,t('First ranking has 10 points'));
    $this->assertEqual($ranking_2->getPoints(),10,t('Second ranking has 10 points'));
    $this->assertEqual($ranking_3->getPoints(),5,t('Second ranking has 5 points'));
    $this->assertEqual($ranking_4->getPoints(),1,t('Second ranking has 1 points'));

  }
}
