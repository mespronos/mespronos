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

class GameBreadcrumbBuilder implements BreadcrumbBuilderInterface {

    public function applies(RouteMatchInterface $route_match) {
        return $route_match->getCurrentRouteMatch()->getRouteName() == 'entity.game.canonical';
    }

    public function build(RouteMatchInterface $route_match) {
        /** @var \Drupal\mespronos\Entity\Game $game */
        $game = $route_match->getParameter('game');
        $day = $game->getDay();
        $league = $day->getLeague();
        $breadcrumb = new Breadcrumb();
        $breadcrumb->addCacheContexts(['route']);
        $links = [];
        $links[] = Link::createFromRoute(t('Home'), '<front>');
        $links[] = Link::createFromRoute(t('Leagues'), 'mespronos.leagues.list');
        $links[] = Link::createFromRoute($league->label(), 'entity.league.canonical', ['league'=>$league->id()]);
        $links[] = Link::createFromRoute($day->label(), 'entity.day.canonical', ['day'=>$day->id()]);
        return $breadcrumb->setLinks($links);
    }

}