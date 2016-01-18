<?php

/**
 * @file
 * Contains Drupal\mespronos\Tests\MespronosLeagueTest.
 */

namespace Drupal\mespronos\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\mespronos\Entity\Sport;
use Drupal\mespronos\Entity\League;

/**
 * Provides automated tests for the mespronos module.
 * @group mespronos
 */
class MespronosLeagueTest extends WebTestBase {

  public $sport;
  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => "MesPronos League entity functionality",
      'description' => 'Test Unit for entity League from mespronos.',
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
  }

  public function testLeagueSimpleCreation() {

    $league = League::create(array(
      'created' => time(),
      'updated' => time(),
      'creator' => 1,
      'sport' => $this->sport->id(),
      'name' => 'test championnat',
      'betting_type' => 'score',
      'classement' => true,
      'status' => 'active',
    ));

    $this->assertTrue($league->save(),t('Saving league worked, league_id => @league_id',array('@league_id'=>$league->id())));

    $league2 = League::create(array(
      'created' => time(),
      'updated' => time(),
      'creator' => 1,
      'sport' => $this->sport->id(),
      'name' => 'test championnat',
      'betting_type' => 'score',
      'classement' => true,
      'status' => 'active',
    ));
    $league2->save();

    $this->assertNotEqual($league->id(),$league2->id(),t('If we save a new league, ids are different id1 => @id1, id2 => @id2',array('@id1'=>$league->id(),'@id2'=>$league2->id())));
  }

  public function testLeagueCreationWithBadAttributes() {
    try {
      League::create(array(
        'sport' => $this->sport->id(),
        'name' => 'test championnat',
        'betting_type' => 'Lorem',
        'classement' => true,
        'status' => 'active',
      ));
      $this->fail('League should has a correct betting type, throw an exception elsewise');
    } catch (\Exception $e) {
      $this->pass('League without a correct betting type throw an exception');
    }

    try {
      League::create(array(
        'sport' => $this->sport->id(),
        'name' => 'test championnat',
        'betting_type' => 'score',
        'classement' => true,
        'status' => 'ipsum',
      ));
      $this->fail('League should has a correct status, throw an exception elsewise');
    } catch (\Exception $e) {
      $this->pass('League without a correct status throw an exception');
    }
  }

  public function testLeagueCreationWithoutAttributes() {
    try {
      League::create(array(
        'sport' => $this->sport->id(),
        'betting_type' => 'score',
        'classement' => true,
        'status' => 'active',
      ));
      $this->fail('League\'name shouldn\' be null');
    } catch (\Exception $e) {
      $this->pass(t('Unset League\'name throw exception | exception message : @msg',array('@msg'=>$e->getMessage())));
    }

    try {
      League::create(array(
        'sport' => $this->sport->id(),
        'name' => '',
        'betting_type' => 'score',
        'classement' => true,
        'status' => 'active',
      ));
      $this->fail('League\'name shouldn\' be empty');
    } catch (\Exception $e) {
      $this->pass(t('Empty League\'name throw exception | exception message : @msg',array('@msg'=>$e->getMessage())));
    }


    $league = League::create(array(
      'sport' => $this->sport->id(),
      'name' => 'test championnat',
      'classement' => true,
      'status' => 'active',
    ));
    $this->assertNotNull($league->getBettingType(true),t('If no betting type set, a default is set'));
    $this->assertEqual($league->getBettingType(true),League::$betting_type_default_value,t('If no betting type set, default one is used : @betting_type',array('@betting_type'=>$league->getBettingType())));

    $league = League::create(array(
      'sport' => $this->sport->id(),
      'name' => 'test championnat',
      'betting_type' => '',
      'classement' => true,
      'status' => 'active',
    ));
    $this->assertNotNull($league->getBettingType(true),t('If betting type is empty,a default is set'));
    $this->assertEqual($league->getBettingType(true),League::$betting_type_default_value,t('If no betting type set, default one is used : @betting_type',array('@betting_type'=>$league->getBettingType())));

    $league = League::create(array(
      'sport' => $this->sport->id(),
      'name' => 'test championnat',
      'classement' => true,
      'betting_type' => 'score',
    ));
    $this->assertNotNull($league->getStatus(true),t('If betting type is empty,a default is set'));
    $this->assertEqual($league->getStatus(true),League::$status_default_value,t('If no betting type set, default one is used : @betting_type',array('@betting_type'=>$league->getStatus())));

    $league = League::create(array(
      'sport' => $this->sport->id(),
      'name' => 'test championnat',
      'classement' => true,
      'status' => '',
      'betting_type' => 'score',
    ));
    $this->assertNotNull($league->getBettingType(true),t('If betting type is empty,a default is set'));
    $this->assertEqual($league->getStatus(true),League::$status_default_value,t('If no betting type set, default one is used : @betting_type',array('@betting_type'=>$league->getStatus())));

    try {
      League::create(array(
        'name' => 'test championnat',
        'classement' => true,
      ));
      $this->fail('League\'s must not be empty');
    } catch (\Exception $e) {
      $this->pass(t('League\'s sport should be set | exception message : @msg',array('@msg'=>$e->getMessage())));
    }

    try {
      League::create(array(
        'sport' => 47,
        'name' => 'test championnat',
        'classement' => true,
      ));
      $this->fail('League should refere to a sport entity');
    } catch (\Exception $e) {
      $this->pass(t('League\'s sport should be set  | exception message : @msg',array('@msg'=>$e->getMessage())));
    }

  }

}
