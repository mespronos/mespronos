<?php

/**
 * @file
 * Contains Drupal\mespronos\Tests\MespronosLeagueTest.
 */

namespace Drupal\mespronos\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Provides automated tests for the mespronos module.
 * @group mespronos
 */
class MespronosSportTest extends WebTestBase {
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
  }

  public function testSportCreation() {
    $sport_name = 'Football';
    $sport = \Drupal\mespronos\Entity\Sport::create(array(
      'created' => time(),
      'updated' => time(),
      'creator' => 1,
      'name' => $sport_name,
      'langcode' => 'und',
    ));
    $this->assertTrue($sport->save(),t('Saving sport @sport_name worked',array('@sport_name'=>$sport_name)));

    $sport2 = \Drupal\mespronos\Entity\Sport::create(array(
      'created' => time(),
      'updated' => time(),
      'creator' => 1,
      'name' => $sport_name,
      'langcode' => 'und',
    ));
    $this->assertEqual($sport2->id(),$sport->id(),t('We can\'t save a sport with the same name',array('@sport_name'=>$sport_name)));
  }
}
