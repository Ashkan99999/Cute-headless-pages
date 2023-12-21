<?php

namespace Drupal\cute_file\Plugin\rest\resource;

use Drupal\Component\Plugin\DependentPluginInterface;
use Drupal\cute_file\CuteFileServiceInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Represents FileCuteRest records as resources.
 *
 * @RestResource (
 *   id = "cute_file_cute_rest",
 *   label = @Translation("cute rest file resource"),
 *   uri_paths = {
 *     "canonical" = "/api1/cute-file/file/{id}",
 *     "create" = "/api1/cute-file/file"
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
class FileCuteRestResource extends ResourceBase implements DependentPluginInterface {


  /**
   * The CuteFileServiceInterface.
   *
   * @var \Drupal\cute_file\CuteFileServiceInterface
   */
  protected $cuteFileService;

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
   *   The database connection.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, CuteFileServiceInterface $cute_file_service) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->cuteFileService = $cute_file_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->getParameter('serializer.formats'), $container->get('logger.factory')
      ->get('rest'), $container->get('cute_file.file'));
  }

  /**
   * Responds to GET requests.
   *
   * @param int $id
   *   The ID of the record.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The response containing the record.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   */
  public function get($id) {
    $file = $this->cuteFileService->getFile($id);
    if ($file['status'] === 200) {
      return new ModifiedResourceResponse($file['data'], 200);
    }
    else {
      return new ModifiedResourceResponse($file['message'], $file['status']);
    }
  }

  /**
   * Responds to POST requests and saves the new record.
   *
   * @param mixed $data
   *   Data to write into the database.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The HTTP response object.
   */
  //  public function post($data) {
  //
  //
  //    // Return the newly created record in the response body.
  //    return new ModifiedResourceResponse($data, 201);
  //  }

  /**
   * Responds to entity PATCH requests.
   *
   * @param int $id
   *   The ID of the record.
   * @param mixed $data
   *   Data to write into the database.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The HTTP response object.
   */
  //  public function patch($id, $data) {
  //
  //  }

  /**
   * Responds to entity DELETE requests.
   *
   * @param int $id
   *   The ID of the record.
   *
   * @return \Drupal\rest\ModifiedResourceResponse|\Drupal\rest\ResourceResponse
   *   The HTTP response object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   */
  public function delete($id) {
    $file = $this->cuteFileService->deleteFile($id);
    $this->logger->notice('Example record @id has been deleted.', ['@id' => $id]);
    if ($file === 404) {
      return new ModifiedResourceResponse(NULL, 404);
    }
    if ($file === 403) {
      return new ResourceResponse(NULL, 403);
    }
    else {
      return new ModifiedResourceResponse(NULL, 204);
    }
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
    $collection = parent::routes();
    return $collection;
  }

  /**
   * {@inheritdoc}
   */
  protected function getBaseRoute($canonical_path, $method) {
    $route = parent::getBaseRoute($canonical_path, $method);

    // Change ID validation pattern.
    if ($method != 'POST') {
      $route->setRequirement('id', '\d+');
    }

    return $route;
  }

}
