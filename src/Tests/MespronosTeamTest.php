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

/**
 * Provides automated tests for the mespronos module.
 * @group mespronos
 */
class MespronosTeamTest extends WebTestBase {

  public $sport;
  public $league;
  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => "MesPronos Team entity functionality",
      'description' => 'Test Unit for entity Team from mespronos.',
      'group' => 'MesPronos',
    );
  }

  static public $modules = array(
    'mespronos',
  );

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->sport = Sport::create(array(
      'created' => time(),
      'updated' => time(),
      'creator' => 1,
      'name' => 'Football',
      'langcode' => 'und',
    ));
    $this->sport->save();

    $this->league = League::create(array(
      'created' => time(),
      'updated' => time(),
      'creator' => 1,
      'sport' => $this->sport->id(),
      'name' => 'test championnat',
      'betting_type' => 'score',
      'classement' => true,
      'status' => 'active',
    ));
    $this->league->save();

  }

  public function testTeamSimpleCreation() {

    $team = Team::create(array(
      'created' => time(),
      'updated' => time(),
      'creator' => 1,
      'name' => 'Mon Equipe',
      'langcode' => 'und',
    ));
    $team->save();

    $this->assertTrue($team->save(),t('Team saving is returning TRUE, team_id => @team_id',array('@team_id'=>$team->id())));

    $team2 = Team::create(array(
      'created' => time(),
      'updated' => time(),
      'creator' => 1,
      'name' => 'Mon Equipe 2',
      'langcode' => 'und',
    ));
    $team2->save();

    $this->assertNotEqual($team2->id(),$team->id(),t('If we save a new team, ids are different id1 => @id1, id2 => @id2',array('@id1'=>$team->id(),'@id2'=>$team2->id())));
  }

  public function testTeamCreationWithoutName() {
    try {
      Team::create(array(
        'created' => time(),
        'updated' => time(),
        'creator' => 1,
        'langcode' => 'und',
      ));
      $this->fail('Empty Team\'name shouldn\'t be null');
    } catch (\Exception $e) {
      $this->pass(t('Unset team \'name throw exception | exception message : @msg', array('@msg' => $e->getMessage())));
    }
  }

  public function testTeamCreationWithEmptyName() {
    try {
      Team::create(array(
        'created' => time(),
        'updated' => time(),
        'creator' => 1,
        'langcode' => 'und',
        'name' => '',
      ));
      $this->fail('Teams\'name shouldn\' be empty');
    } catch (\Exception $e) {
      $this->pass(t('Empty Teams\'name throw exception | exception message : @msg', array('@msg' => $e->getMessage())));
    }
  }

  public function testTeamCreationWithOnlySpaceInName() {
    try {
      Team::create(array(
        'created' => time(),
        'updated' => time(),
        'creator' => 1,
        'langcode' => 'und',
        'name' => ' ',
      ));
      $this->fail('Teams\'name (with spaces) shouldn\' be empty');
    } catch (\Exception $e) {
      $this->pass(t('Empty Teams\'name (with spaces) throw exception | exception message : @msg', array('@msg' => $e->getMessage())));
    }
  }
}
