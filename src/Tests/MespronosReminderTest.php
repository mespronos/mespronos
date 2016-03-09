<?php

/**
 * @file
 * Contains Drupal\mespronos\Tests\MespronosReminderTest.
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
class MespronosReminderTest extends WebTestBase {
  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => "MesPronos Reminder functionality",
      'description' => 'Test Unit for reminder features.',
      'group' => 'MesPronos',
    );
  }

  static public $modules = array(
    'mespronos',
  );
  
  public function setUp() {
    parent::setUp();
  }

}
