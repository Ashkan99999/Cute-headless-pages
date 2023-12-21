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
 *   id = "gcbv_rest_resource",
 *   label = @Translation("cute Get children by vid"),
 *   uri_paths = {
 *     "canonical" = "/api1/cute/child-by-vid/{vid}/{parent}/{dep}"
 *   }
 * )
 */
class GetChildrenByVidRestResource extends ResourceBase {

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
   *   Throws exception expected.
   *
   * @param $vid - The taxonomy vocabulary machine name.
   * @param $parent - 0 for all, 1 for parent, 2 for child
   * @param $dep - depth if all
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   */
  public function get($vid, $parent = NULL, $dep = NULL) {
    $parent = $parent == 'all' ? 0 : $parent;
    $dep = $dep == 'all' ? 0 : $dep;
    $chid = Drupal::service('cute_taxonomy.taxonomy')
      ->getChildrenByVid($vid, $parent, $dep);
    if ($chid['status'] == 200) {
      return new ModifiedResourceResponse($chid, 200);
    }
    return new ModifiedResourceResponse($chid['message'], $chid['status']);
  }

}
