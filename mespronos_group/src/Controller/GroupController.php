<?php

namespace Drupal\mespronos_group\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Render\Renderer;

/**
 * Class GroupController.
 *
 * @package Drupal\mespronos_group\Controller
 */
class GroupController extends ControllerBase {


  /**
   * Drupal\Core\Render\Renderer definition.
   *
   * @var Drupal\Core\Render\Renderer
   */
  protected $renderer;
  /**
   * {@inheritdoc}
   */
  public function __construct(Renderer $renderer) {
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer')
    );
  }

  /**
   * List.
   *
   * @return string
   *   Return Hello string.
   */
  public function groupList() {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Implement method: list')
    ];
  }

}
