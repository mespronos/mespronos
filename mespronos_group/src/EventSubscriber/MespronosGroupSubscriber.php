<?php

/**
 * @file
 * Definition of Drupal\r4032login\EventSubscriber\R4032LoginSubscriber.
 */

namespace Drupal\mespronos_group\EventSubscriber;

use Drupal\Core\Routing\CurrentRouteMatch;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Redirect 403 to User Login event subscriber.
 */
class MespronosGroupSubscriber implements EventSubscriberInterface {

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch $route_match
   */
  protected $route_match;


  /**
   * Constructs a new R4032LoginSubscriber.
   *
   * @param \Drupal\Core\Routing\CurrentRouteMatch $route_match
   *
   */
  public function __construct(CurrentRouteMatch $route_match) {
    $this->route_match = $route_match;
  }

  /**
   * Redirects on 403 Access Denied kernel exceptions.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The Event to process.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   *   Thrown when the access is denied and redirects to user login page.
   */
  public function onKernelException(GetResponseEvent $event) {
    $exception = $event->getException();
    if (!($exception instanceof AccessDeniedHttpException)) {
      return;
    }
    if ($this->route_match->getRouteName() != 'entity.group.canonical') {
      return;
    }
    drupal_set_message(t('You are not a member of this group'), 'warning');
    $group = $this->route_match->getParameter('group');
    $response = new RedirectResponse(\Drupal::url('mespronos_group.group.join', ['group'=>$group->id()]));
    $event->setResponse($response);
  }

  /**
   * {@inheritdoc}
   *
   * The priority for the exception must be as low as possible this subscriber
   * to respond with AccessDeniedHttpException.
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[KernelEvents::EXCEPTION][] = array('onKernelException');
    return $events;
  }

}
