<?php

namespace Drupal\cute_content\Plugin\rest\resource;

use Drupal;
use Drupal\Component\Plugin\DependentPluginInterface;
use Drupal\Core\Database\Connection;
use Drupal\cute_content\contentServiceinterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Represents Content records as resources.
 *
 * @RestResource (
 *   id = "cute_content_content",
 *   label = @Translation("Content"),
 *   uri_paths = {
 *     "canonical" = "/api1/cute-content/content/{id}",
 *     "create" = "/api1/cute-content/content"
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
class ContentResource extends ResourceBase implements DependentPluginInterface
{

  /**
   * The database connection.
   *
   * @var Connection
   */
  protected $dbConnection;

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
   * @param Connection $db_connection
   *   The database connection.
   */
  protected $contentService;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, Connection $db_connection, contentServiceinterface $contentService)
  {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->dbConnection = $db_connection;
    $this->contentService = $contentService;
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
      $container->get('database'),
      $container->get('cute_content.content')
    );
  }

  /**
   * Responds to GET requests.
   *
   * @param mixed $id
   *   The ID of the record.
   *
   * @return ModifiedResourceResponse
   *   The response containing the record.
   *
   * @throws HttpException
   */
  public function get($id)
  {
    $node = $this->contentService->getNode($id);
    if ($node['status'] == 200) {
      return new ModifiedResourceResponse($node['data'], 200);
    } elseif ($node['status'] == 403) {
      throw new AccessDeniedHttpException($node['message']);

    } else {
      throw new NotFoundHttpException($node['message']);
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
    $node = Drupal::service('cute_content.content')->createNode($data);
    if ($node['status'] == 200) {
      return new ModifiedResourceResponse($node['data'], 200);
    }
    return new ModifiedResourceResponse($node['data'], $node['status']);
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
    $node = $this->contentService->updateNode($id, $data);
    if ($node['status'] == 200) {
      return new ModifiedResourceResponse($node['data'], 200);
    } else {
      return new ModifiedResourceResponse($node['data'], $node['status']);
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
    //delete node
    $node = $this->contentService->deleteNode($id);
    $request = Response::HTTP_FORBIDDEN;
    return new ModifiedResourceResponse(NULL, $node['status']);
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
