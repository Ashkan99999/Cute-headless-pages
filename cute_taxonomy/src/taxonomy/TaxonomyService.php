<?php

namespace Drupal\cute_taxonomy\taxonomy;

use Drupal;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * Class TaxonomyService.
 */
class TaxonomyService implements TaxonomyServiceInterface {


  /**
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   */
  public function __construct(AccountProxyInterface $currentUser, EntityTypeManagerInterface $entity_type_manager) {
    $this->currentUser = $currentUser;
    $this->entityTypeManager = $entity_type_manager;
  }


  /**
   * @param $vid
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getVocabulary($vid) {
    $vocabulary = Drupal::entityTypeManager()
      ->getStorage('taxonomy_vocabulary')
      ->load($vid);
    if ($vocabulary) {
      $access = $this->currentUser->hasPermission('administer taxonomy');
      if ($access) {
        return ["status" => 200, "data" => $vocabulary->toArray()];
      }
      else {
        return [
          "status" => 403,
          "message" => t('You do not have permission to access this page.'),
        ];
      }
    }
    return ["status" => 404, "message" => t('Vocabulary not found.')];
  }

  /**
   * @param $tid
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getTerm($tid) {
    $term = Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->load($tid);
    if ($term) {
      $access = $this->checkAccess($term, 'view', $this->currentUser);
      if ($access) {
        return ["status" => 200, "data" => $term->toArray()];
      }
      else {
        return [
          "status" => 403,
          "message" => t('You do not have permission to access this page.'),
        ];
      }
    }
    return ["status" => 404, "message" => t('Term not found.')];
  }

  /**
   * @param $term
   * @param $operation
   * @param $user
   *
   * @return mixed
   */
  public function checkAccess($term, $operation, $user) {
    return $term->access($operation, $user);
  }

  /**
   * @param $vid
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getTerms($vid) {
    $terms = Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadByProperties(['vid' => $vid]);

    if (count($terms) > 0) {
      $access = $this->checkAccessVac($vid, 'view', $this->currentUser);
      if ($access) {
        $getTerms = [];
        foreach ($terms as $term) {
          $getTerms[] = [
            "id" => $term->id(),
            'name' => $term->getName(),
            'parent' => $term->get('parent')->target_id,
          ];
        }
        return [
          "status" => 200,
          "data" => $getTerms,
        ];
      }
      else {
        return [
          "status" => 403,
          "message" => t('You do not have permission to access this page.'),
        ];
      }
    }
    return ["status" => 404, "message" => t('Terms not found.')];
  }

  /**
   * @param $vid
   * @param $operation
   * @param $user
   *
   * @return bool|\Drupal\Core\Access\AccessResultInterface
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function checkAccessVac($vid, $operation, $user) {
    $vocabulary = Drupal::entityTypeManager()
      ->getStorage('taxonomy_vocabulary')
      ->load($vid);
    $ac = $vocabulary->access('create', $user);
    if ($vocabulary) {
      return $vocabulary->access($operation, $user);
    }
    return FALSE;
  }

  /**
   * @param $data
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createTerm($data) {
    $access = $this->checkAccessVac($data['vid'], 'create', $this->currentUser);
    if ($access) {
      $term = Drupal::entityTypeManager()
        ->getStorage('taxonomy_term')
        ->create($data);
      $term->save();
      return ["status" => 200, "data" => $term->toArray()];
    }
    else {
      return [
        "status" => 403,
        "message" => t('You do not have permission to access this page.'),
      ];
    }
  }

  /**
   * @param $tid
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function deleteTerm($tid) {
    $term = Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->load($tid);
    if ($term) {
      $vid = $term->get('vid')->getString();
      $access = $this->checkAccessVac($vid, 'delete', $this->currentUser);
      if ($access) {
        $term->delete();
        return ["status" => 200, "data" => t('Term deleted.')];
      }
      else {
        return [
          "status" => 403,
          "message" => t('You do not have permission to access this page.'),
        ];
      }
    }
    return ["status" => 404, "message" => t('Term not found.')];
  }


  /**
   * @param $tid
   * @param $data
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function updateTerm($tid, $data) {
    $term = Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->load($tid);

    if ($term) {
      $access = $this->checkAccess($term, 'update', $this->currentUser);
      if ($access) {
       foreach ($data as $key => $value) {
          $term->set($key, $value);
        }
        $term->save();
        return ["status" => 200, "data" => $term->toArray()];
      }
      else {
        return [
          "status" => 403,
          "message" => t('You do not have permission to access this page.'),
        ];
      }
    }
    return ["status" => 404, "message" => t('Term not found.')];
  }

  /**
   * @param $tid
   */
  public function getChildren($tid) {
    $getTid = Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->load($tid);
    $vid = $getTid->get('vid')->getString();
    $terms = Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadByProperties(['vid' => $vid]);
    $getTerms = [];
    foreach ($terms as $term) {
      if ($term->get('parent')->target_id == $tid) {
        // check access
        $access = $this->checkAccess($term, 'view', $this->currentUser);
        if ($access) {
          $getTerms[] = $term->toArray();
        }
      }
    }
    if (count($getTerms) > 0) {
      return ["status" => 200, "data" => $getTerms];
    }
    else {
      return ["status" => 404, "message" => t('Terms not found.')];
    }
  }

  /**
   * @param $tid
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getParent($tid) {
    $parent = Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadParents($tid);
    $parent = reset($parent);
    if ($parent) {
      return [
        "status" => 200,
        "data" => [
          'parent' => $parent->id(),
          'name' => $parent->label(),
        ],
      ];
    }
    return [
      "status" => 404,
      "message" => t('Term not found.'),
    ];

  }

  /**
   * @param $vid
   * @param $parent
   * @param $depth
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getChildrenByVid($vid, $parent, $depth) {
    $results[] = [
      'id' => "0",
      'name' => t('root original'),
      'parent' => "0",
      'children' => [],
    ];
    // for first level
    if ($parent == 0) {
      $items = Drupal::entityTypeManager()
        ->getStorage('taxonomy_term')
        ->loadTree($vid, $parent, 1, TRUE);
      foreach ($items as $item) {
        $results[] = [
          'id' => $item->id(),
          'name' => $item->label(),
          'parent' => $item->get('parent')->getString(),
          'children' => $this->generateTree($vid, $item->id(), 1),
        ];
      }
    }
    else {
      $items = Drupal::entityTypeManager()
        ->getStorage('taxonomy_term')
        ->loadTree($vid, $parent, $depth, TRUE);
      foreach ($items as $item) {
        $results[] = [
          'id' => $item->id(),
          'name' => $item->label(),
          'parent' => $item->get('parent')->getString(),
          'depth' => $item->depth,
          'children' => $this->generateTree($vid, $item->id(), 1),
        ];
      }
    }
    if (count($results) > 0) {
      return [
        "status" => 200,
        "data" => $results,
      ];
    }
    $data [] = [
      'id' => "0",
      'name' => t('root original'),
      'parent' => "0",
      'children' => [],
    ];
    return [
      "status" => 200,
      "data" => $data,
    ];
  }

  public function generateTree($vid, $parent, $depth) {

    $items = Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadTree($vid, $parent, $depth, TRUE);
    $tree = [];
    foreach ($items as $item) {
      $tree[] = [
        'id' => $item->id(),
        'name' => $item->label(),
        'parent' => $item->get('parent')->getString(),
        'children' => $this->generateTree($vid, $item->id(), 1),
      ];
    }
    return $tree;
  }

  /**
   * @param $vid
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getParenstByVid($vid) {
    $tree = Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadTree($vid, 0, NULL, TRUE);

    $results = [0 => t('root')];
    foreach ($tree as $term) {
      $results[$term->id()] = $term->getName();
    }
    if (count($results) > 0) {
      return [
        "status" => 200,
        "data" => $results,
      ];
    }
    return [
      "status" => 404,
      "message" => t('Term not found.'),
    ];
  }

  public function filterTaxonomy($data) {
    //filter content
    $query = $this->entityTypeManager->getStorage('taxonomy_term')->getQuery();

    $query->condition('vid', $data['vid']);

    if (isset($data['field'])) {
      foreach ($data['field'] as $key => $value) {
        $query->condition($key, $value['value'], $value['operator']);
      }
    }
    $tid = $query->execute();
    $taxonomys = $this->entityTypeManager->getStorage('taxonomy_term')
      ->loadMultiple($tid);
    $finalTaxonomy = [];
    foreach ($taxonomys as $taxonomy) {

      $access = $this->checkAccess($taxonomy, 'view', $this->currentUser);
      if ($access) {
        $finalTaxonomy[] = $taxonomy->toArray();
      }
    }
    if (count($finalTaxonomy) > 0) {
      return ["status" => 200, "data" => $finalTaxonomy];
    }
    return [
      "status" => 404,
      "message" => t('Node not found.'),
    ];
  }

}
