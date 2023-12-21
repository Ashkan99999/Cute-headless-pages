<?php

namespace Drupal\cute_user\Plugin\rest\resource;

use Drupal;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "get_name_user_rest_resource",
 *   label = @Translation("Get name user rest resource"),
 *   uri_paths = {
 *     "canonical" = "/api1/cute-user/getname/{uid}"
 *   }
 * )
 */
class GetNameUserRestResource extends ResourceBase {

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
   * @param string $uid
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The HTTP response object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function get($uid) {
    $userName = Drupal::service('cute_user.user')->getUserName($uid);
    if ($userName['status'] == 200) {
      return new Drupal\rest\ModifiedResourceResponse($userName['data'], 200);
    }
    return new Drupal\rest\ModifiedResourceResponse($userName['data'], 403);
  }

}
