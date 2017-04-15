<?php

namespace Drupal\Tests\mespronos_group\Unit;

use Drupal\KernelTests\KernelTestBase;
use Drupal\mespronos_group\Entity\Group;

/**
 * Tests Mespronos Group
 *
 * @ingroup mespronos_group
 *
 * @group mespronos
 * @group mespronos_group
 */
class MespronosGroupKernelTest extends KernelTestBase {

  public static $modules = ['mespronos', 'mespronos_group', 'user'];

  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('group');
  }

  public function testBasic() {
    $this->assertEquals(2, 1 + 1, '1 + 1 = 2');
  }

  public function testGroupSavingReturnTrue() {
    $group = Group::create([
      'name'=> 'test',
      'code'=> 'test',
    ]);
    $this->assertTrue($group->save(), t('Group saving return true'));
  }
}