<?php

namespace Drupal\Tests\mespronos_group\Unit;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Tests\UnitTestCase;
use Drupal\mespronos_group\Entity\Group;

/**
 * Mespronos Group Unit tests
 *
 * @ingroup mespronos
 *
 * @group mespronos
 * @group mespronos_group
 */
class MespronosGroupTest extends UnitTestCase {

  protected function setUp() {
    parent::setUp();

    $container = new ContainerBuilder();

    $entity_manager = $this->getMock('Drupal\Core\Entity\EntityManagerInterface');
    $container->set('entity.manager', $entity_manager);

    \Drupal::setContainer($container);
  }

  public function testBasic() {
    $this->assertEquals(2, 1 + 1, '1 + 1 = 2');
  }

}