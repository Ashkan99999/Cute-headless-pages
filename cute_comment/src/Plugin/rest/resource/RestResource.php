<?php

namespace Drupal\cute_comment\Plugin\rest\resource;

use Drupal\Component\Plugin\DependentPluginInterface;
use Drupal\cute_comment\CommentServiceInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Represents CommentRestResource records as resources.
 *
 * @RestResource (
 *   id = "cute_comment_rest",
 *   label = @Translation("cute comment rest"),
 *   uri_paths = {
 *     "canonical" = "/api1/cute-comment/comment/{id}",
 *     "create" = "/api1/cute-comment/comment"
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
class RestResource extends ResourceBase implements DependentPluginInterface {


  /**
   * The comment service.
   *
   * @var \Drupal\cute_comment\CommentServiceInterface
   */
  protected $commentService;

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
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, CommentServiceInterface $commentService) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->commentService = $commentService;
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
      $container->get('cute_comment.comment')
    );
  }

  /**
   * Responds to GET requests.
   *
   * @param int $id
   *   The ID of the record.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing the record.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   */
  public function get($id) {
    $comment = $this->commentService->getComment($id);
    return new ResourceResponse($comment['data'], $comment['status']);
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
    $comment = $this->commentService->createComment($data);
    return new ModifiedResourceResponse($comment['data'], $comment['status']);
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
    $comment = $this->commentService->updateComment($id, $data);
    return new ModifiedResourceResponse($comment['data'], $comment['status']);
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
    $comment = $this->commentService->deleteComment($id);
    return new ModifiedResourceResponse(NULL, 204);
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
