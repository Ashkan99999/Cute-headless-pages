<?php

namespace Drupal\cute_taxonomy\Plugin\rest\resource\taxonomy;

use Drupal;
use Drupal\Component\Plugin\DependentPluginInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Routing\BcRoute;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Represents crudTaxonamy records as resources.
 *
 * @RestResource (
 *   id = "cute_rest_crudtaxonomy",
 *   label = @Translation("crudTaxonomy"),
 *   uri_paths = {
 *     "canonical" = "/api1/cute-rest/crud/taxonomy/{id}",
 *     "create" =  "/api1/cute-rest/crud/taxonomy"
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
class CrudTaxonomyResource extends ResourceBase implements DependentPluginInterface {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
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
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Database\Connection $db_connection
   *   The database connection.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, Connection $db_connection) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->dbConnection = $db_connection;
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
      $container->get('database')
    );
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
    $term = Drupal::service('cute_taxonomy.taxonomy')->getTerm($id);
    if ($term["status"] == 200) {
      return new ModifiedResourceResponse($term, 200);
    }
    else {
      return new ModifiedResourceResponse($term['message'], $term["status"]);
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
  public function post($data) {
    $term = Drupal::service('cute_taxonomy.taxonomy')->createTerm($data);
    if ($term["status"] == 200) {
      return new ModifiedResourceResponse($term, 201);
    }
    else {
      return new ModifiedResourceResponse($term['message'], $term["status"]);
    }
  }



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
  public function patch($id, $data) {
    $term = Drupal::service('cute_taxonomy.taxonomy')->updateTerm($id, $data);

    if ($term["status"] == 200) {
      return new ModifiedResourceResponse($term, 200);
    }
    else {
      return new ModifiedResourceResponse($term['message'], $term["status"]);
    }
  }


  /**
   * Responds to entity DELETE requests.
   *
   * @param int $id
   *   The ID of the record.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The HTTP response object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   */
  public function delete($id) {

    $term = Drupal::service('cute_taxonomy.taxonomy')->deleteTerm($id);
    if ($term["status"] == 200) {
      return new ModifiedResourceResponse(NULL, 200);
    }
    else {
      return new ModifiedResourceResponse(NULL, $term["status"]);
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
    return parent::routes();
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
