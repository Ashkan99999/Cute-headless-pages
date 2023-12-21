<?php

namespace Drupal\cute_user\Plugin\rest\resource;

use Drupal\cute_user\UserServiceInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Represents RegisterByEmail records as resources.
 *
 * @RestResource (
 *   id = "cute_user_registerbyemail",
 *   label = @Translation("RegisterByEmail"),
 *   uri_paths = {
 *     "create" = "/api/cute-user/registerbyemail"
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
class RegisterbyemailResource extends ResourceBase
{

  /**
   * The user service.
   *
   * @var UserServiceInterface
   */
  protected $userService;


  /**
   * {@inheritdoc}
   */
  public function __construct(
    array                $configuration,
                         $plugin_id,
                         $plugin_definition,
    array                $serializer_formats,
    LoggerInterface      $logger,
    UserServiceInterface $userService
  )
  {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->userService = $userService;
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
      $container->get('cute_user.user')
    );
  }

  /**
   * Responds to POST requests and saves the new record.
   *
   * @param array $data
   *   Data to write into the database.
   *
   * @return ModifiedResourceResponse
   *   The HTTP response object.
   */
  public function post(array $data)
  {
    $user = $this->userService->registerByEmail($data);

    return new ModifiedResourceResponse($user, 201);
  }


  /**
   * {@inheritdoc}
   */
  protected function getBaseRoute($canonical_path, $method)
  {
    $route = parent::getBaseRoute($canonical_path, $method);
    // Set ID validation pattern.
    if ($method != 'POST') {
      $route->setRequirement('id', '\d+');
    }
    return $route;
    // test comment 2  for
  }

}
