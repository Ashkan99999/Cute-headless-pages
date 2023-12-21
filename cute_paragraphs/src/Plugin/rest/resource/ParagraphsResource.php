<?php

namespace Drupal\cute_paragraphs\Plugin\rest\resource;

use Drupal;
use Drupal\Component\Plugin\DependentPluginInterface;
use Drupal\Core\Database\Connection;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Represents Paragraphs records as resources.
 *
 * @RestResource (
 *   id = "cute_paragraphs_paragraphs",
 *   label = @Translation("Paragraphs"),
 *   uri_paths = {
 *     "canonical" = "/api1/cute-paragraphs/paragraphs/{id}",
 *     "create" = "/api1/cute-paragraphs/paragraphs"
 *   }
 * )
 *
 * @DCG
 * This plugin exposes database records as REST resources. In order to enable
 *   it
 * import the resource configuration into active configuration storage. You may
 * find an example of such configuration in the following file:
 * core/modules/rest/config/optional/rest.resource.entity.node.yml.
 * Alternatively you can make use of REST UI module.
 * @see https://www.drupal.org/project/restui
 * For accessing Drupal entities through REST interface use
 * \Drupal\rest\Plugin\rest\resource\EntityResource plugin.
 */
class ParagraphsResource extends ResourceBase implements DependentPluginInterface
{


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
   * @param LoggerInterface $logger
   *   A logger instance.
   *   The database connection.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger)
  {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
  {
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
   * @param int $id
   *   The ID of the record.
   *
   * @return ModifiedResourceResponse
   *   The response containing the record.
   *
   * @throws HttpException
   */
  public function get($id)
  {
    $paragraphs = Drupal::service('cute_paragraphs.paragraphs')->get($id);
    if ($paragraphs['status'] == 200) {
      return new ModifiedResourceResponse($paragraphs['data'], 200);
    } else {
      return new ModifiedResourceResponse($paragraphs['message'], $paragraphs['status']);
    }
  }

  /**
   * Responds to POST requests and saves the new record.
   *
   * @param mixed $data
   *   Data to write into the database.
   *
   * @return ModifiedResourceResponse
   *   The HTTP response object.
   */
  public function post($data)
  {
    $paragraphs = Drupal::service('cute_paragraphs.paragraphs')->create($data);
    if ($paragraphs['status'] == 200) {
      return new ModifiedResourceResponse($paragraphs['data'], 200);
    }
    return new ModifiedResourceResponse($paragraphs['message'], $paragraphs['status']);
  }

  /**
   * Responds to entity PATCH requests.
   *
   * @param int $id
   *   The ID of the record.
   * @param mixed $data
   *   Data to write into the database.
   *
   * @return ModifiedResourceResponse
   *   The HTTP response object.
   */
  public function patch($id, $data)
  {
    $paragraphs = Drupal::service('cute_paragraphs.paragraphs')
      ->update($id, $data);
    if ($paragraphs['status'] == 200) {
      return new ModifiedResourceResponse($paragraphs['data'], 200);
    } else {
      return new ModifiedResourceResponse($paragraphs['message'], $paragraphs['status']);
    }
  }

  /**
   * Responds to entity DELETE requests.
   *
   * @param int $id
   *   The ID of the record.
   *
   * @return ModifiedResourceResponse
   *   The HTTP response object.
   *
   * @throws HttpException
   */
  public function delete($id)
  {
    $paragraphs = Drupal::service('cute_paragraphs.paragraphs')->delete($id);
    if ($paragraphs['status'] == 200) {
      return new ModifiedResourceResponse(null, 200);
    } else {
      return new ModifiedResourceResponse($paragraphs['message'], $paragraphs['status']);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies()
  {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function routes()
  {
    return parent::routes();
  }

  /**
   * {@inheritdoc}
   */
  protected function getBaseRoute($canonical_path, $method)
  {
    $route = parent::getBaseRoute($canonical_path, $method);

    // Change ID validation pattern.
    if ($method != 'POST') {
      $route->setRequirement('id', '\d+');
    }

    return $route;
  }

}
