<?php

namespace Drupal\cute_user\Plugin\rest\resource;

use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\cute_user\UserServiceInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Represents MobileNumber records as resources.
 *
 * @RestResource (
 *   id = "cute_user_mobilenumber",
 *   label = @Translation("MobileNumber"),
 *   uri_paths = {
 *     "create" = "/api/cute-user/mobilenumber"
 *   }
 * )
 *
 * @DCG
 * The plugin exposes key-value records as REST resources. In order to enable it
 * import the resource configuration into active configuration storage. An
 * example of such configuration can be located in the following file:
 * core/modules/rest/config/optional/rest.resource.entity.node.yml.
 * Alternatively you can enable it through admin interface provider by REST UI
 * module.
 * @see https://www.drupal.org/project/restui
 *
 * @DCG
 * Notice that this plugin does not provide any validation for the data.
 * Consider creating custom normalizer to validate and normalize the incoming
 * data. It can be enabled in the plugin definition as follows.
 * @code
 *   serialization_class = "Drupal\foo\MyDataStructure",
 * @endcode
 *
 * @DCG
 * For entities, it is recommended to use REST resource plugin provided by
 * Drupal core.
 * @see \Drupal\rest\Plugin\rest\resource\EntityResource
 */
class MobilenumberResource extends ResourceBase {

  /**
   * The key-value storage.
   *
   * @var \Drupal\Core\KeyValueStore\KeyValueStoreInterface
   */
  protected $storage;

  /**
   * The user service.
   *
   * @var \Drupal\cute_user\UserServiceInterface
   */
  protected $userService;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array                    $configuration,
                             $plugin_id,
                             $plugin_definition,
    array                    $serializer_formats,
    LoggerInterface          $logger,
    KeyValueFactoryInterface $keyValueFactory,
    UserServiceInterface     $userService
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger, $keyValueFactory);
    $this->storage = $keyValueFactory->get('cute_user_mobilenumber');
    $this->userService = $userService;
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
      $container->get('keyvalue'),
      $container->get('cute_user.user')
    );
  }

  /**
   * Responds to POST requests and saves the new record.
   *
   * @param array $data
   *   Data to write into the database.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The HTTP response object.
   */
  public function post(array $data) {

    if ($data['steep'] == '1') {
      $code = $this->userService->sendCode($data);
      return new ModifiedResourceResponse($code['data'], $code['status']);
    }
    elseif ($data['steep'] == '2') {
      $response = $this->userService->checkCode($data['mobileNumber'], $data['code']);
      return new ModifiedResourceResponse($response, 201);
    }
  }


  /**
   * {@inheritdoc}
   */
  protected function getBaseRoute($canonical_path, $method) {
    $route = parent::getBaseRoute($canonical_path, $method);
    // Set ID validation pattern.
    if ($method != 'POST') {
      $route->setRequirement('id', '\d+');
    }
    return $route;
  }


}
