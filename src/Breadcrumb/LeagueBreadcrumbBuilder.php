<?php

/**
 * @file
 * Contains Drupal\mespronos\Breadcrumb\LeagueBreadcrumbBuilder.
 */

namespace Drupal\mespronos\Breadcrumb;

use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Drupal\Core\Breadcrumb\Breadcrumb;

class LeagueBreadcrumbBuilder implements BreadcrumbBuilderInterface {

    public function applies(RouteMatchInterface $route_match) {
        return $route_match->getCurrentRouteMatch()->getRouteName() == 'mespronos.league.index';
    }

    public function build(RouteMatchInterface $route_match) {
        /** @var \Drupal\mespronos\Entity\Day $day */
        $breadcrumb = new Breadcrumb();
        $breadcrumb->addCacheContexts(['route']);
        $links = [];
        $links[] = Link::createFromRoute(t('Home'), '<front>');
        $links[] = Link::createFromRoute(t('Leagues'), 'mespronos.leagues.list');
        return $breadcrumb->setLinks($links);
    }

}