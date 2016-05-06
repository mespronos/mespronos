<?php

/**
 * @file
 * Contains Drupal\mespronos\Tests\MespronosGroupCreationTest.
 */

namespace Drupal\mespronos_group\Tests;

use Drupal\mespronos_group\Entity\Group;
use Drupal\simpletest\WebTestBase;

/**
 * Provides automated tests for the mespronos module.
 * @group mespronos
 */
class MespronosGroupCreationTest extends WebTestBase {
  public $group;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => "MesPronos Group testing",
      'description' => 'Test Group creation',
      'group' => 'MesPronos',
    );
  }

  static public $modules = array(
    'mespronos','mespronos_group'
  );

  public function setUp() {
    parent::setUp();
  }

  public function testCreationGroup() {
    $group = Group::create([
        'name'=> 'test',
        'code'=> 'test',
    ]);
    $this->assertTrue($group->save(),t('Group saving return true'));
  }



}
