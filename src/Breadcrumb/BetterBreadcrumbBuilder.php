<?php

namespace Drupal\mespronos\Breadcrumb;

use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Drupal\Core\Breadcrumb\Breadcrumb;

/**
 * Class BetterBreadcrumbBuilder.
 *
 * @package Drupal\mespronos\Breadcrumb
 */
class BetterBreadcrumbBuilder implements BreadcrumbBuilderInterface {

  /**
   * @inheritdoc
   */
  public function applies(RouteMatchInterface $route_match) {
    return in_array($route_match->getCurrentRouteMatch()->getRouteName(), ['entity.user.canonical']);
  }

  /**
   * @inheritdoc
   */
  public function build(RouteMatchInterface $route_match) {
    /** @var \Drupal\mespronos\Entity\Day $day */
    $breadcrumb = new Breadcrumb();
    $breadcrumb->addCacheContexts(['route']);
    $links = [];
    $links[] = Link::createFromRoute(t('Home'), '<front>');
    $links[] = Link::createFromRoute(t('Betters'), 'mespronos.ranking');
    return $breadcrumb->setLinks($links);
  }

}
