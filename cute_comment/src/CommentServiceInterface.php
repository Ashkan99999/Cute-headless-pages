<?php

namespace Drupal\cute_comment;

/**
 * Interface CommentServiceInterface.
 */
interface CommentServiceInterface {

  public function getComment($id);

  public function getComments($nid);

  public function createComment($comment);

  public function updateComment($id, $comment);

  public function deleteComment($id);

}
