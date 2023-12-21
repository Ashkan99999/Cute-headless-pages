<?php

namespace Drupal\cute_paragraphs;

/**
 * Interface ParagraphsServiceInterface.
 */
interface ParagraphsServiceInterface {

  public function create($data);

  public function get($rid);

  public function delete($rid);

  public function update($rid, $data);

}
