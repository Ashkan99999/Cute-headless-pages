<?php

namespace Drupal\cute_content;


use Drupal;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * Class ContentService.
 */
class ContentService implements ContentServiceInterface {


  /**
   * Drupal\Core\Session\AccountProxyInterface definition.
   *
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
   * Constructs a new ContentService object.
   */
  public function __construct(AccountProxyInterface $current_user, EntityTypeManagerInterface $entity_type_manager) {
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
  }

  public function updateNode($id, $data) {

    $node = $this->entityTypeManager->getStorage('node')->load($id);
    if ($node) {
      $access = $this->checkAccess($node, 'update');
      if ($access) {
        foreach ($data as $key => $value) {
          $node->set($key, $value);
        }
        $node->save();
        return ["status" => 200, "data" => $node->toArray()];
      }
      else {
        return [
          "status" => 403,
          "message" => t('You do not have permission to access this page.'),
        ];
      }
    }
    return [
      "status" => 404,
      "message" => t('Node not found.'),
    ];
  }

  private function checkAccess($node, $op) {
    //check access to node
    return $node->access($op, $this->currentUser);
  }

  public function getNode($nid) {
    //get node
    $node = $this->entityTypeManager->getStorage('node')->load($nid);
    if ($node) {
      $access = $this->checkAccess($node, 'view');
      if ($access) {
        return ["status" => 200, "data" => $node->toArray()];
      }
      else {
        return [
          "status" => 403,
          "message" => t('You do not have permission to access this page.'),
        ];
      }
    }
    return [
      "status" => 404,
      "message" => t('Node not found.'),
    ];
  }

  public function deleteNode($nid) {
    //delete node
    $node = $this->entityTypeManager->getStorage('node')->load($nid);
    if ($node) {
      $access = $this->checkAccess($node, 'delete');
      if ($access) {
        $node->delete();
        return ["status" => 204, "message" => t('Node deleted.')];
      }
      return [
        "status" => 403,
        "message" => t('You do not have permission to access this page.'),
      ];
    }
    else {
      return [
        "status" => 404,
        "message" => t('Node not found.'),
      ];
    }
  }

  public function createNode($data) {
    //create node

    $access = $this->checkAccessByType($data['type'], 'create');
    if ($access) {
      $node = $this->entityTypeManager->getStorage('node')->create($data);
      $node->save();
      return ["status" => 200, "data" => $node->toArray()];
    }
    else {
      return [
        "status" => 403,
        "message" => t('You do not have permission to create this node.'),
      ];
    }
  }

  public function checkAccessByType($type, $op) {
    if ($op == 'create') {
      return $this->entityTypeManager->getAccessControlHandler('node')
        ->createAccess($type, $this->currentUser);
    }
    return FALSE;
  }

  public function getNodeByContentType($type) {
    //get node by content type
    $nodes = $this->entityTypeManager->getStorage('node')
      ->loadByProperties(
        [
          'type' => $type,
          'status' => 1,
        ]
      );
    return $this->getNodeHas($nodes, $type);
  }

  public function getNodeHas($nodes, $type) {
    if ($nodes) {
      $data = [];
      foreach ($nodes as $node) {
        $access = $this->checkAccess($node, 'view');
        if ($access) {
          //get node id
          $data[] = [
            'id' => $node->id(),
            'title' => $node->getTitle(),
            'uid' => $node->getOwnerId(),
            'created' => $node->getCreatedTime(),
            'changed' => $node->getChangedTime(),
            'author' => Drupal::service('cute_user.user')
              ->getUserName($node->getOwnerId())['data'],
          ];
        }
      }
      if (count($data) == 0) {
        return [
          "status" => 403,
          "message" => t('You do not have permission to access this page.'),
        ];
      }
      return ["status" => 200, "data" => $data];
    }
    return [
      "status" => 404,
      "message" => t('Node not found.'),
    ];
  }

  public function getNodeByContentTypeAndFields($type, $fields, $value, $sort) {
    //get node by content type and fields and sort by value
    $query = $this->entityTypeManager->getStorage('node')->getQuery();
    $query->condition('type', $type);
    $query->condition($fields, $value);
    $query->sort($sort);
    $nids = $query->execute();
    $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
    return $this->getNodeHas($nodes, $type);
  }

  public function getNodeByUser($uid, $type) {
    //get node by user
    $nodes = $this->entityTypeManager->getStorage('node')
      ->loadByProperties(['uid' => $uid, 'type' => $type]);
    return $this->getNodeHas($nodes, $type);
  }

  public function filterContent($data) {
    $finalNode = [];
    $total = $this->entityTypeManager->getStorage('node')->getQuery();
    $total->condition('type', $data['type']);
    if (isset($data['field'])) {
      foreach ($data['field'] as $key => $value) {
        $total->condition($key, $value['value'], $value['operator']);
      }
    }
    if(isset($data['relation'])){

      foreach ($data['relation'] as $key => $value) {
        $total->condition($key, $value['value'], $value['operator']);
      }
    }
    $total->count();
    $total = $total->execute();
    $totalPage = ceil($total / $data['limit']);
    $finalNode['pager']['total'] = $total;
    $finalNode['pager']['totalPage'] = $totalPage;
    $finalNode['pager']['current'] = $data['page'];

    //filter content
    $query = $this->entityTypeManager->getStorage('node')->getQuery();
    $query->condition('type', $data['type']);
    if (isset($data['field'])) {
      foreach ($data['field'] as $key => $value) {
        $query->condition($key, $value['value'], $value['operator']);
      }
    }
    if(isset($data['relation'])){

      foreach ($data['relation'] as $key => $value) {
        $query->condition($key, $value['value'], $value['operator']);
      }
    }

    $query->sort('changed', 'DESC');
    // range
    $query->range($data['page'], $data['limit']);

    $nids = $query->execute();


    $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);

    foreach ($nodes as $node) {
      $access = $this->checkAccess($node, 'view');
      if ($access) {
        $finalNode['rows'][] = $node;
      }
    }
    if (count($finalNode) > 0) {
      return ["status" => 200, "data" => $finalNode];
    }
    return [
      "status" => 404,
      "message" => t('Node not found.'),
    ];
  }

}
