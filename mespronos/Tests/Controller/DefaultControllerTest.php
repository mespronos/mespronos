<?php

/**
 * @file
 * Contains Drupal\mespronos\Tests\DefaultController.
 */

namespace Drupal\mespronos\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Provides automated tests for the mespronos module.
 */
class DefaultControllerTest extends WebTestBase {
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

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * Tests mespronos functionality.
   */
  public function testDefaultController() {
    // Check that the basic functions of module mespronos.
    $this->assertEqual(TRUE, TRUE, 'Test Unit Generated via App Console.');
  }

}
