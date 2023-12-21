<?php

namespace Drupal\cute_headless\EventSubscriber;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * cute headless event subscriber.
 */
class CuteHeadlessSubscriber implements EventSubscriberInterface {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * Constructs a CuteHeadlessSubscriber object.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user.
   */
  public function __construct(RouteMatchInterface $route_match, AccountInterface $account) {
    $this->routeMatch = $route_match;
    $this->account = $account;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      KernelEvents::REQUEST => ['onKernelRequest'],
    ];
  }

  /**
   * Kernel request event handler.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   Response event.
   */
  public function onKernelRequest(RequestEvent $event) {
    // if user is not logged in, then redirect to login page.

    if (!$this->account->isAuthenticated()) {
      $route = [
        'user.login',
        'rest.cute_user_mobilenumber.POST',
        'system.csrftoken',
        'cute_user.user_register',
        'rest.cute_user_register.POST',
        'oauth2_token.token',
        'system.4xx',
      ];

      $route_name = $this->routeMatch->getRouteName();

      if (!in_array($route_name, $route)) {
        // redirect to login page 301
        $response = new RedirectResponse('/user/login', 301);
        $event->setResponse($response);
      }
    }
  }

}
