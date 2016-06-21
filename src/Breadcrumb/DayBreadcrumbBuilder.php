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

class DayBreadcrumbBuilder implements BreadcrumbBuilderInterface {

    public function applies(RouteMatchInterface $route_match) {
        return $route_match->getCurrentRouteMatch()->getRouteName() == 'entity.day.canonical';
    }

    public function build(RouteMatchInterface $route_match) {
        /** @var \Drupal\mespronos\Entity\day $day */
        $day = $route_match->getParameter('day');
        $league = $day->getLeague();
        $breadcrumb = new Breadcrumb();
        $breadcrumb->addCacheContexts(['route']);
        $links = [];
        $links[] = Link::createFromRoute(t('Home'), '<front>');
        $links[] = Link::createFromRoute(t('Leagues'), 'mespronos.leagues.list');
        $links[] = Link::createFromRoute($league->label(), 'mespronos.league.index',['league'=>$league->id()]);
        return $breadcrumb->setLinks($links);
    }

}