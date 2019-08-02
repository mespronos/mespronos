<?php
namespace Drupal\Tests\mespronos\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\mespronos\Entity\Day;
use Drupal\mespronos\Entity\League;
use Drupal\mespronos\Entity\Sport;
use Drupal\mespronos\Entity\Team;

/**
 * Test mespronos league manager service
 *
 * @group mespronos
 */
abstract class MespronosKernelTestBase extends KernelTestBase {

  public static $modules = ['user', 'mespronos', 'text', 'options', 'system', 'datetime'];

  public function setUp() {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installEntitySchema('sport');
    $this->installEntitySchema('league');
    $this->installEntitySchema('day');
    $this->installEntitySchema('team');
    $this->installEntitySchema('game');
    $this->installEntitySchema('bet');
  }

}
