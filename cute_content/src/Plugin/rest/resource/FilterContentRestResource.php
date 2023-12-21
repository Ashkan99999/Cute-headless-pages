<?php

namespace Drupal\cute_content\Plugin\rest\resource;

use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "filter_content_rest_resource",
 *   label = @Translation("Filter content rest resource"),
 *   uri_paths = {
 *     "create" = "/api1/cute-content/filter"
 *   }
 * )
 */
class FilterContentRestResource extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;


  /**
   * The content service.
   *
   * @var \Drupal\cute_content\ContentServiceInterface
   */
  protected $contentService;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->logger = $container->get('logger.factory')->get('cute_content');
    $instance->currentUser = $container->get('current_user');
    $instance->contentService = $container->get('cute_content.content');
    return $instance;
  }


  /**
   * Responds to POST requests.
   *
   * Returns a list of bundles for specified entity.
   *
   * @param array $data
   *   The data array.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The HTTP response object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post(array $data) {
    $response = $this->contentService->filterContent($data);
    if ($response['status'] == 200) {
      return new ModifiedResourceResponse($response['data'], 200);
    }
    return new ModifiedResourceResponse($response['data'], $response['status']);
  }

}
