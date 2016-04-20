<?php

/**
 * @file
 * Contains Drupal\mespronos\Tests\MespronosReminderTest.
 */

namespace Drupal\mespronos\Tests;

use Drupal\mespronos\Controller\ReminderController;
use Drupal\simpletest\WebTestBase;
use Drupal\mespronos\Entity\Reminder;

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

  public function testReminderInitReturnTrue() {
    $this->assertFalse(ReminderController::init());
    $this->assertTrue(is_array(ReminderController::getHoursDefined()));
    $this->assertEqual(count(ReminderController::getHoursDefined()),0);

    \Drupal::configFactory()->getEditable('mespronos.reminder')->set('hours',['48'=>48,'24'=>24])->save();

    \Drupal::configFactory()->getEditable('mespronos.reminder')->set('enabled',TRUE)->save();

    $this->assertTrue(ReminderController::init());
    $this->assertTrue(is_array(ReminderController::getHoursDefined()));
    $this->assertEqual(count(ReminderController::getHoursDefined()),2);
  }

}
