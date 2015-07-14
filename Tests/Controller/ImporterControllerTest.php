<?php

/**
 * @file
 * Contains Drupal\mespronos\Tests\ImporterController.
 */

namespace Drupal\mespronos\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Provides automated tests for the mespronos module.
 */
class ImporterControllerTest extends WebTestBase {
  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => "mespronos ImporterController's controller functionality",
      'description' => 'Test Unit for module mespronos and controller ImporterController.',
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
  public function testImporterController() {
    // Check that the basic functions of module mespronos.
    $this->assertEqual(TRUE, TRUE, 'Test Unit Generated via App Console.');
  }

}
