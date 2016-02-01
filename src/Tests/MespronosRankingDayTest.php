<?php

/**
 * @file
 * Contains Drupal\mespronos\Tests\MespronosLeagueTest.
 */

namespace Drupal\mespronos\Tests;

use Drupal\mespronos\Entity\RankingDay;
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
class MespronosRankingDayTest extends WebTestBase {
  public $sport;
  public $league;
  public $team1;
  public $team2;
  public $day;
  public $game;
  public $bet;
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
    $this->team2 = Team::create(['name' => 'team2']);
    $this->team3 = Team::create(['name' => 'team3']);
    $this->team4 = Team::create(['name' => 'team4']);

    $this->team1->save();
    $this->team2->save();
    $this->team3->save();
    $this->team4->save();

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

    $this->bet = Bet::create(array(
      'better' => 1,
      'game' => $this->game->id(),
      'score_team_1' => 1,
      'score_team_2' => 1,
      'points' => 10,
    ));
    $this->bet->save();
  }

  public function testCreationRankingDay() {
    $rankingDay = RankingDay::create([
      'better' => 1,
      'day' => $this->day->id(),
      'games_betted' => 5,
      'points' => 10
    ]);
    $this->assertTrue($rankingDay->save(),t('Ranking day saving return true'));
  }

  public function testMethodRemoveRankingDay() {
    $this->assertEqual(0,RankingDay::removeRanking($this->day),t('Remove ranking return 0 when no ranking exit'));
    $rankingDay = RankingDay::create([
      'better' => 1,
      'day' => $this->day->id(),
      'games_betted' => 5,
      'points' => 10
    ]);
    $rankingDay->save();
    $this->assertEqual(1,RankingDay::removeRanking($this->day),t('Remove ranking return 1 when a ranking exit'));
    $this->assertEqual(0,RankingDay::removeRanking($this->day),t('And then 0 after deletion'));
  }

  public function testCreationWithExistingBet() {
    //Cleanup the shit from other tests
    RankingDay::createRanking($this->day);

    $dataFetched = RankingDay::getData($this->day);
    debug($dataFetched);
    $this->assertEqual(1,count($dataFetched),t('Data fetched is an array of 1 line'));
    $dataRow = array_pop($dataFetched);
    $this->assertEqual(10,$dataRow->points,t('Points are right'));
    $this->assertEqual(1,$dataRow->better,t('better is right'));
    $this->assertEqual(1,$dataRow->nb_bet,t('bet number is right'));
  }

  public function testRankingPosition() {

    $better_1 = $this->drupalCreateUser();
    $better_2 = $this->drupalCreateUser();

    $dateO = new \DateTime();
    $date = $dateO->format('Y-m-d\TH:i:s');

    $day = Day::create(array(
      'league' => $this->league->id(),
      'number' => 2,
      'name' => 'day test',
    ));

    $day->save();

    $game = Game::create(array(
      'team_1' => $this->team1->id(),
      'team_2' => $this->team2->id(),
      'day' => $day->id(),
      'game_date' => $date,
    ));
    $game->save();

    $betGood = Bet::create(array(
      'better' => $better_1->id(),
      'game' => $game->id(),
      'score_team_1' => 1,
      'score_team_2' => 1,
    ));
    $betGood->save();

    $betWrong = Bet::create(array(
      'better' => $better_2->id(),
      'game' => $game->id(),
      'score_team_1' => 1,
      'score_team_2' => 0,
    ));
    $betWrong->save();

    $game->setScore(1,1);

    $this->assertTrue($game->isScoreSetted(),t('Game score is setted'));

    $game->save();

    //on reload les bet
    $betGood = Bet::load($betGood->id());
    $betWrong = Bet::load($betWrong->id());

    $this->assertEqual($betGood->getPoints(),10,t('A good bet worth 10 points'));
    $this->assertEqual($betWrong->getPoints(),1,t('A bad bet worth 1 points'));

    RankingDay::createRanking($day);

    $ranking = RankingDay::getRankingForDay($day);

    $this->assertEqual(count($ranking),2,t('A ranking with two better contains two lines'));

    $r1 = array_shift($ranking);
    $r2 = array_shift($ranking);

    $this->assertTrue($r1->getPoints()>$r2->getPoints(),t('First ranking has more points than the second one'));
    $this->assertTrue($r1->getPosition()<$r2->getPosition(),t('First ranking has position less greater than the second'));

    $game2 = Game::create(array(
      'team_1' => $this->team3->id(),
      'team_2' => $this->team4->id(),
      'day' => $day->id(),
      'game_date' => $date,
    ));
    $game2->save();

    $bet2Good = Bet::create(array(
      'better' => $better_1->id(),
      'game' => $game2->id(),
      'score_team_1' => 1,
      'score_team_2' => 1,
    ));
    $bet2Good->save();

    $bet2Wrong = Bet::create(array(
      'better' => $better_2->id(),
      'game' => $game2->id(),
      'score_team_1' => 1,
      'score_team_2' => 0,
    ));
    $bet2Wrong->save();

    RankingDay::createRanking($day);
    $ranking = RankingDay::getRankingForDay($day);

    $this->assertEqual(count($ranking),2,t('With a second game, A ranking with two better contains still two lines'));

    $r1 = array_shift($ranking);

    $this->assertEqual($r1->getGameBetted(),1,t('The number of betted games coresponding to bet with score (1)'));

    $game2->setScore(1,1);
    $game2->save();

    //on reload les bet
    $bet2Good = Bet::load($bet2Good->id());
    $bet2Wrong = Bet::load($bet2Wrong->id());

    $this->assertEqual($bet2Good->getPoints(),10,t('A good bet worth 10 points'));
    $this->assertEqual($bet2Wrong->getPoints(),1,t('A bad bet worth 1 points'));

    RankingDay::createRanking($day);
    $ranking = RankingDay::getRankingForDay($day);

    $this->assertEqual(count($ranking),2,t('With a second game, A ranking with two better contains still two lines'));

    $r1 = array_shift($ranking);
    $this->assertEqual($r1->getGameBetted(),2,t('The number of betted games coresponding to bet with score (2) now we set the second game score'));
  }

}
