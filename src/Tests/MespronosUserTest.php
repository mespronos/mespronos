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
class MespronosUserTest extends WebTestBase {
  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => "MesPronos User functionality",
      'description' => 'Test Unit for user permissions.',
      'group' => 'MesPronos',
    );
  }

  static public $modules = array(
    'mespronos',
  );

  public function testAnonymousPermissions() {
    $this->user = $this->drupalCreateUser();
    $this->drupalLogin($this->user);
  }

  public function testAuthenticatedPermissions() {

    $this->user = $this->drupalCreateUser();
    $this->assertTrue($this->user->hasPermission('view next bets days'),'User has permission "view next bets days"');
    $this->assertTrue($this->user->hasPermission('view last bets days'),'User has permission "view last bets days"');
    $this->assertTrue($this->user->hasPermission('subscribe to league'),'User has permission "subscribe to league"');
    $this->assertTrue($this->user->hasPermission('make a bet'),'User has permission "make a bet"');

    $this->assertFalse($this->user->hasPermission('set marks'),'User has not the permission "set marks"');
    $this->assertFalse($this->user->hasPermission('import league content'),'User has not the permission "import league content"');
  }
}
