<?php

namespace Drupal\cute_taxonomy\Plugin\rest\resource;

use Drupal\Component\Plugin\DependentPluginInterface;
use Drupal\cute_taxonomy\taxonomy\TaxonomyServiceInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Represents FilterTaxonomyRestResource records as resources.
 *
 * @RestResource (
 *   id = "cute_taxonomy_filtertaxonomy",
 *   label = @Translation("FilterTaxonomyRestResource"),
 *   uri_paths = {
 *     "create" = "/api1/cute-taxonomy/filter-taxonomy"
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
class FiltertaxonomyResource extends ResourceBase implements DependentPluginInterface
{


  protected $taxonomyService;

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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, TaxonomyServiceInterface $taxonomyService)
  {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->taxonomyService = $taxonomyService;
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
      $container->get('cute_taxonomy.taxonomy')
    );
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
    $response = $this->taxonomyService->filterTaxonomy($data);
    if ($response['status'] == 200) {
      return new ModifiedResourceResponse($response['data'], 200);
    }
    return new ModifiedResourceResponse($this->t('No data found'), 404);
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
