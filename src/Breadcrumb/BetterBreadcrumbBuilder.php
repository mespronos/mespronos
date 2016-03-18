<?php

/**
 * @file
 * Contains Drupal\mespronos\Breadcrumb\BetterBreadcrumbBuilder.
 */

namespace Drupal\mespronos\Breadcrumb;

use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Drupal\Core\Breadcrumb\Breadcrumb;

class BetterBreadcrumbBuilder implements BreadcrumbBuilderInterface {

    public function applies(RouteMatchInterface $route_match) {
        return in_array($route_match->getCurrentRouteMatch()->getRouteName(),['entity.user.canonical']);
    }

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