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
  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => "mespronos DefaultController's controller functionality",
      'description' => 'Test Unit for module mespronos and controller DefaultController.',
      'group' => 'Other',
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

}
