<?php

namespace Drupal\cute_content\Plugin\rest\resource;

use Drupal;
use Drupal\Component\Plugin\DependentPluginInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Represents getNodeByContentType records as resources.
 *
 * @RestResource (
 *   id = "cute_content_getnodebycontenttype",
 *   label = @Translation("getNodeByContentType"),
 *   uri_paths = {
 *     "canonical" = "/api1/cute-content/getnodebycontenttype/{type}",
 *   }
 * )
 *
 * @DCG
 * This plugin exposes database records as REST resources. In order to enable it
 * import the resource configuration into active configuration storage. You may
 * find an example of such configuration in the following file:
 * core/modules/rest/config/optional/rest.resource.entity.node.yml.
 * Alternatively you can make use of REST UI module.
 * @see https://www.drupal.org/project/restui
 * For accessing Drupal entities through REST interface use
 * \Drupal\rest\Plugin\rest\resource\EntityResource plugin.
 */
class GetnodebycontenttypeResource extends ResourceBase implements DependentPluginInterface {


  /**
   * Constructs a Drupal\rest\Plugin\rest\resource\EntityResource object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('rest'),
    );
  }

  /**
   * Responds to GET requests.
   *
   * @param string $type
   *   The ID of the record.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The response containing the record.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   */
  public function get($type) {
    $node = Drupal::service('cute_content.content')
      ->getNodeByContentType($type);
    if ($node['status'] == 200) {
      return new ModifiedResourceResponse($node['data'], 200);
    }
    return new ModifiedResourceResponse($node['data'], $node['status']);
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function routes() {
    return parent::routes();
  }

}
