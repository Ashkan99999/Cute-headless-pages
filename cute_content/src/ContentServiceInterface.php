<?php

namespace Drupal\cute_content;

/**
 * Interface ContentServiceInterface.
 */
interface ContentServiceInterface {

  public function getNode($nid);

  public function getNodeByContentType($type);

  public function getNodeByContentTypeAndFields($type, $fields, $value, $sort);

  public function updateNode($id, $data);

  public function createNode($data);

  public function deleteNode($nid);

  public function getNodeByUser($uid, $type);

  public function filterContent($data);

}
