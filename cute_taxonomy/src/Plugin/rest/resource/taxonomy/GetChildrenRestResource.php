<?php

namespace Drupal\cute_taxonomy\Plugin\rest\resource\taxonomy;

use Drupal;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "get_children_rest_resource",
 *   label = @Translation("cute Get children"),
 *   uri_paths = {
 *     "canonical" = "/api1/cute/get-chidren/{tid}"
 *   }
 * )
 */
class GetChildrenRestResource extends ResourceBase {

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
    $instance->logger = $container->get('logger.factory')->get('cute_rest');
    $instance->currentUser = $container->get('current_user');
    return $instance;
  }

  /**
   * Responds to GET requests.
   *
   * @param $tid
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   */
  public function get($tid) {
    $chidren = Drupal::service('cute_taxonomy.taxonomy')->getChildren($tid);
    if ($chidren['status'] == 200) {
      return new ModifiedResourceResponse($chidren['data'], 200);
    }
    return new ModifiedResourceResponse($chidren['message'], $chidren['status']);
  }

}
