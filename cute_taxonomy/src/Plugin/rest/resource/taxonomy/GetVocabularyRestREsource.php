<?php

namespace Drupal\cute_taxonomy\Plugin\rest\resource\taxonomy;

use Drupal;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "get_vocabulary_rest_resource",
 *   label = @Translation("cute rest Get vocabulary"),
 *   uri_paths = {
 *     "canonical" = "/api1/cute/get-vocabulary/{vid}",
 *   }
 * )
 */
class GetVocabularyRestREsource extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->logger = $container->get('logger.factory')->get('cute_rest');
    $instance->currentUser = $container->get('current_user');
    return $instance;
  }


  /**
   * @param $vid
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   */
  public function get($vid) {
    $vac = Drupal::service('cute_taxonomy.taxonomy')->getVocabulary($vid);
    if ($vac['status'] == 200) {
      return new ModifiedResourceResponse($vac['data'], 200);
    }
    return new ModifiedResourceResponse($vac['message'], $vac['status']);
  }

}
