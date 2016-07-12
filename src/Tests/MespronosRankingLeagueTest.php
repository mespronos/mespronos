<?php

/**
 * @file
 * Contains Drupal\mespronos\Tests\MespronosLeagueTest.
 */

namespace Drupal\mespronos\Tests;

use Drupal\mespronos\Entity\RankingDay;
use Drupal\mespronos\Controller\RankingController;
use Drupal\mespronos\Entity\RankingGeneral;
use Drupal\mespronos\Entity\RankingLeague;
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
class MespronosRankingLeagueTest extends WebTestBase {
  public $sport;
  public $league1;
  public $league2;
  public $league1day1;
  public $league1day2;
  public $league2day1;
  public $league2day2;
  public $team1;
  public $team2;
  public $team3;
  public $team4;
  public $l1d1game1;
  public $l1d2game2;
  public $l2d1game3;
  public $l2d2game4;
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

    $this->league1 = League::create(array(
      'sport' => $this->sport->id(),
      'name' => 'test championnat 1',
      'betting_type' => 'score',
      'classement' => true,
      'status' => 'active',
    ));
    $this->league1->save();

    $this->league2 = League::create(array(
      'sport' => $this->sport->id(),
      'name' => 'test championnat 2',
      'betting_type' => 'score',
      'classement' => true,
      'status' => 'active',
    ));
    $this->league2->save();

    $this->team1 = Team::create(['name' => 'team1']);
    $this->team1->save();
    $this->team2 = Team::create(['name' => 'team2']);
    $this->team2->save();
    $this->team3 = Team::create(['name' => 'team3']);
    $this->team3->save();
    $this->team4 = Team::create(['name' => 'team4']);
    $this->team4->save();


    $this->league1day1 = Day::create(array(
      'league' => $this->league1->id(),
      'number' => 1,
    ));
    $this->league1day1->save();

    $this->league1day2 = Day::create(array(
      'league' => $this->league1->id(),
      'number' => 2,
    ));
    $this->league1day2->save();

    $this->league2day1 = Day::create(array(
      'league' => $this->league2->id(),
      'number' => 1,
    ));
    $this->league2day1->save();

    $this->league2day2 = Day::create(array(
      'league' => $this->league2->id(),
      'number' => 2,
    ));
    $this->league2day2->save();

    $this->better1 = $this->drupalCreateUser();
    $this->better2 = $this->drupalCreateUser();
    $this->better3 = $this->drupalCreateUser();
    $this->better4 = $this->drupalCreateUser();

    $dateO = new \DateTime();
    $date = $dateO->format('Y-m-d\TH:i:s');

    $this->l1d1game1 = Game::create(array(
      'team_1' => $this->team1->id(),
      'team_2' => $this->team2->id(),
      'day' => $this->league1day1->id(),
      'game_date' => $date,
    ));
    $this->l1d1game1->save();

    $this->l1d2game2 = Game::create(array(
      'team_1' => $this->team3->id(),
      'team_2' => $this->team4->id(),
      'day' => $this->league1day2->id(),
      'game_date' => $date,
    ));
    $this->l1d2game2->save();

    $this->l2d1game3 = Game::create(array(
      'team_1' => $this->team1->id(),
      'team_2' => $this->team2->id(),
      'day' => $this->league2day1->id(),
      'game_date' => $date,
    ));
    $this->l2d1game3->save();

    $this->l2d2game4 = Game::create(array(
      'team_1' => $this->team3->id(),
      'team_2' => $this->team4->id(),
      'day' => $this->league2day2->id(),
      'game_date' => $date,
    ));
    $this->l2d2game4->save();
  }

  public function testSimpleWithOnlyOneDay() {
    $betsDay1 = [];
    $betsDay1[] = Bet::create(array(
      'better' => $this->better1->id(),
      'game' => $this->l1d1game1->id(),
      'score_team_1' => 1,
      'score_team_2' => 1,
    ));
    $betsDay1[] = Bet::create(array(
      'better' => $this->better2->id(),
      'game' => $this->l1d1game1->id(),
      'score_team_1' => 1,
      'score_team_2' => 1,
    ));
    $betsDay1[] = Bet::create(array(
      'better' => $this->better3->id(),
      'game' => $this->l1d1game1->id(),
      'score_team_1' => 1,
      'score_team_2' => 1,
    ));
    $betsDay1[] = Bet::create(array(
      'better' => $this->better4->id(),
      'game' => $this->l1d1game1->id(),
      'score_team_1' => 1,
      'score_team_2' => 1,
    ));

    foreach($betsDay1 as $bet) {
      $bet->save();
    }

    $this->l1d1game1->setScore(1,1)->save();

    $this->assertTrue($this->l1d1game1->isScoreSetted(),t('Game1 score is setted'));

    $points = $this->league1->getPoints();
    foreach($betsDay1 as $bet) {
      $bet = Bet::load($bet->id());
      $this->assertEqual($bet->getPoints(),$points['points_score_found'],t('good bets worth 10 points'));
    }

    RankingDay::createRanking($this->league1day1);
    $ranking_day_1 = RankingDay::getRankingForDay($this->league1day1);

    $this->assertEqual(count($ranking_day_1),4,t('Day 1 : @nb betters, so ranking contains @nb lines',array('@nb'=>count($betsDay1))));

    RankingLeague::createRanking($this->league1);
    $ranking_league_1 = RankingLeague::getRankingForLeague($this->league1);

    $this->assertEqual(count($ranking_league_1),4,t('League 1 : @nb betters, so ranking contains @nb lines',array('@nb'=>count($betsDay1))));

    //DAY 2
    $betsDay2 = [];
    $betsDay2[] = Bet::create(array(
      'better' => $this->better1->id(),
      'game' => $this->l1d2game2->id(),
      'score_team_1' => 1,
      'score_team_2' => 1,
    ));
    $betsDay2[] = Bet::create(array(
      'better' => $this->better2->id(),
      'game' => $this->l1d2game2->id(),
      'score_team_1' => 1,
      'score_team_2' => 1,
    ));
    $betsDay2[] = Bet::create(array(
      'better' => $this->better3->id(),
      'game' => $this->l1d2game2->id(),
      'score_team_1' => 1,
      'score_team_2' => 1,
    ));

    foreach($betsDay2 as $bet) {
      $bet->save();
    }

    $this->l1d2game2->setScore(1,1)->save();

    $this->assertTrue($this->l1d2game2->isScoreSetted(),t('Game1 score is setted'));


    foreach($betsDay2 as $bet) {
      $bet = Bet::load($bet->id());
      $this->assertEqual($bet->getPoints(),$points['points_score_found'],t('good bets worth 10 points'));
    }

    RankingDay::createRanking($this->league1day2);
    $ranking_day_2 = RankingDay::getRankingForDay($this->league1day2);

    $this->assertEqual(count($ranking_day_2),count($betsDay2),t('Day 1 : @nb betters, so ranking contains @nb lines',array('@nb'=>count($betsDay2))));

    RankingLeague::createRanking($this->league1);
    $ranking_league_1 = RankingLeague::getRankingForLeague($this->league1);
    $this->assertEqual(count($ranking_league_1),max(count($betsDay2),count($betsDay1)),t('League 1 : @nb betters, so ranking contains @nb lines',array('@nb'=>max(count($betsDay2),count($betsDay1)))));

    $rankingBetter1League1 = RankingLeague::getRankingForBetter($this->better1,$this->league1);
    $this->assertEqual($rankingBetter1League1->getGameBetted(),2,t('Better 1 has betted on two games'));
    $this->assertEqual($rankingBetter1League1->getPoints(),$points['points_score_found']*2,t('Better 1 has 20 points'));
    $this->assertEqual($rankingBetter1League1->getPosition(),1,t('Better 1 is first'));

    $rankingBetter4League1 = RankingLeague::getRankingForBetter($this->better4,$this->league1);
    $this->assertEqual($rankingBetter4League1->getGameBetted(),1,t('Better 1 has betted on 1 games'));
    $this->assertEqual($rankingBetter4League1->getPoints(),($points['points_score_found']),t('Better 1 has 5 points'));
    $this->assertEqual($rankingBetter4League1->getPosition(),4,t('Better 4 is fourth'));

    //DAY 3
    $betsDay3 = [];
    $betsDay3[] = Bet::create(array(
      'better' => $this->better1->id(),
      'game' => $this->l2d1game3->id(),
      'score_team_1' => 2,
      'score_team_2' => 1,
    ));
    $betsDay3[] = Bet::create(array(
      'better' => $this->better2->id(),
      'game' => $this->l2d1game3->id(),
      'score_team_1' => 2,
      'score_team_2' => 1,
    ));
    $betsDay3[] = Bet::create(array(
      'better' => $this->better3->id(),
      'game' => $this->l2d1game3->id(),
      'score_team_1' => 2,
      'score_team_2' => 1,
    ));

    foreach($betsDay3 as $bet) {
      $bet->save();
    }

    $this->l2d1game3->setScore(2,1)->save();

    $this->assertTrue($this->l2d1game3->isScoreSetted(),t('Game3 score is setted'));

    foreach($betsDay3 as $bet) {
      $bet = Bet::load($bet->id());
      $this->assertEqual($bet->getPoints(),$points['points_score_found'],t('good bets worth 10 points'));
    }

    RankingDay::createRanking($this->league2day1);
    $ranking_day_3 = RankingDay::getRankingForDay($this->league2day1);

    $this->assertEqual(count($ranking_day_3),count($betsDay2),t('Day 1 : @nb betters, so ranking contains @nb lines',array('@nb'=>count($betsDay2))));

    RankingLeague::createRanking($this->league1);
    $ranking_league_1 = RankingLeague::getRankingForLeague($this->league1);
    $this->assertEqual(count($ranking_league_1),max(count($betsDay2),count($betsDay1)),t('League 1 has no change : @nb betters, so ranking contains @nb lines',array('@nb'=>max(count($betsDay2),count($betsDay1)))));

    RankingLeague::createRanking($this->league2);
    $ranking_league_2 = RankingLeague::getRankingForLeague($this->league2);
    $this->assertEqual(count($ranking_league_2),count($betsDay3),t('League 2 : @nb betters, so ranking contains @nb lines',array('@nb'=>count($betsDay3))));

    $rankingBetter1League1 = RankingLeague::getRankingForBetter($this->better1,$this->league1);
    $this->assertEqual($rankingBetter1League1->getGameBetted(),2,t('Better 1 has betted on two games on league 1'));
    $this->assertEqual($rankingBetter1League1->getPoints(),$points['points_score_found']*2,t('Better 1 has 20 points'));
    $this->assertEqual($rankingBetter1League1->getPosition(),1,t('Better 1 is first'));

    $rankingBetter1League2 = RankingLeague::getRankingForBetter($this->better1,$this->league2);
    $this->assertEqual($rankingBetter1League2->getGameBetted(),1,t('Better 1 has betted on one game on league 2'));
    $this->assertEqual($rankingBetter1League2->getPoints(),$points['points_score_found'],t('Better 1 has 5 points'));
    $this->assertEqual($rankingBetter1League2->getPosition(),1,t('Better 1 is first'));


  }
}
