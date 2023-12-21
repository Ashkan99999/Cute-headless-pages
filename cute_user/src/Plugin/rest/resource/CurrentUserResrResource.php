<?php

namespace Drupal\cute_user\Plugin\rest\resource;

use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "current_user_resr_resource",
 *   label = @Translation("Current user resr resource"),
 *   uri_paths = {
 *     "canonical" = "/api1/cute-user/current-user"
 *   }
 * )
 */
class CurrentUserResrResource extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->logger = $container->get('logger.factory')->get('cute_user');
    $instance->currentUser = $container->get('current_user');
    return $instance;
  }

    /**
     * Responds to GET requests.
     *
     *
     * @return \Drupal\rest\ModifiedResourceResponse
     *   The HTTP response object.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     *   Throws exception expected.
     */
    public function get() {
      $user = \Drupal::service('cute_user.user')->getCurrentUser();
      if ($user) {
        return new ModifiedResourceResponse($user, 200);
      }
      return new ModifiedResourceResponse("Access denied", 403);
    }

}




