<?php

/**
 * @file
 * Contains Drupal\mespronos\Tests\MespronosLeagueTest.
 */

namespace Drupal\mespronos\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\mespronos\Entity\Sport;

/**
 * Provides automated tests for the mespronos module.
 * @group mespronos
 */
class MespronosSportTest extends WebTestBase {
  public $user;
  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => "MesPronos Sport entity functionality",
      'description' => 'Test Unit for entity Sport from mespronos.',
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
  }

  public function testSportCreation() {
    $sport_name = 'Football';
    $sport = Sport::create(array(
      'created' => time(),
      'updated' => time(),
      'creator' => 1,
      'name' => $sport_name,
      'langcode' => 'und',
    ));
    $this->assertTrue($sport->save(),t('Saving sport @sport_name worked',array('@sport_name'=>$sport_name)));

    $sport2 = Sport::create(array(
      'created' => time(),
      'updated' => time(),
      'creator' => 1,
      'name' => $sport_name,
      'langcode' => 'und',
    ));
    $this->assertEqual($sport2->id(),$sport->id(),t('If we tried to create a sport with an existing name, ti load the existing. id1 => @id1, id2 => @id2',array('@sport_name'=>$sport_name,'@id1'=>$sport->id(),'@id2'=>$sport2->id())));
  }

  public function testCreationOfSportWithoutSportName() {
    try {
      $sport3 = Sport::create(array(
        'created' => time(),
        'updated' => time(),
        'creator' => 1,
        'langcode' => 'und',
      ));
      $this->fail('An unset name should throw an exception');
    } catch (\Exception $e) {
      $this->pass('An unset name throw an exception');
    }
  }

  public function testCreationOfSportWithEmptySportName() {
    try {
      $sport3 = Sport::create(array(
        'created' => time(),
        'updated' => time(),
        'creator' => 1,
        'name' => '',
        'langcode' => 'und',
      ));
      $this->fail('An empty name should throw an exception');
    } catch (\Exception $e) {
      $this->pass('An empty name throw an exception');
    }
  }

  public function testCreationOfSportWithOnlySpaceInName() {
    try {
      $sport3 = Sport::create(array(
        'created' => time(),
        'updated' => time(),
        'creator' => 1,
        'name' => '  ',
        'langcode' => 'und',
      ));
      $this->fail('An empty name should throw an exception');
    } catch (\Exception $e) {
      $this->pass('An empty name throw an exception');
    }
  }

  public function testCreationOfSportWithOnlyTheName() {
    $this->user = $this->drupalCreateUser();

    $this->drupalLogin($this->user);

    $sport = Sport::create(array(
      'name' => 'Rugby',
    ));
    $this->assertTrue($sport->save(),t('Saving sport with only the name works'));
    $this->assertTrue($sport->id()>0,t('Saved sport has an id => @id',array('@id'=>$sport->id())));
    $this->assertTrue($sport->getOwnerId() == $this->user->id() ,t('Saved sport has a creator => @id',array('@id'=>$sport->getOwnerId())));
    $this->assertTrue($sport->getCreatedTime() > 0 ,t('Saved sport has a created time => @id',array('@id'=>$sport->getCreatedTime())));
    $this->assertTrue($sport->getChangedTime() > 0 ,t('Saved sport has a created time => @id',array('@id'=>$sport->getChangedTime())));

  }
}
