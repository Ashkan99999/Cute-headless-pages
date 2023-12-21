<?php

namespace Drupal\cute_comment;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * Class CommentService.
 */
class CommentService implements CommentServiceInterface {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Drupal\Core\Session\AccountProxyInterface definition.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a new CommentService object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, AccountProxyInterface $current_user) {
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
  }

  public function createComment($comment) {
    $data = [
      'entity_type' => $comment['entity_type'],
      'entity_id' => $comment['entity_id'],
      'field_name' => $comment['field_name'],
      'uid' => $this->currentUser->id(),
      'comment_type' => $comment['comment_type'],
    ];
    if (isset($comment['subject'])) {
      $data['subject'] = $comment['subject'];
    }
    if (isset($comment['comment_body'])) {
      $data['comment_body'] = $comment['comment_body'];
    }
    if (isset($comment['fields'])) {
      foreach ($comment['fields'] as $key => $value) {
        $data[$key] = $value;
      }
    }
    $access = $this->entityTypeManager->getAccessControlHandler('comment')
      ->createAccess($comment['comment_type'], $this->currentUser, [
        'entity_type' => $comment['entity_type'],
        'entity_id' => $comment['entity_id'],
      ]);
    if ($access) {
      $comment = $this->entityTypeManager->getStorage('comment')->create($data);
      $comment->save();
      return [
        'status' => '201',
        'data' => $comment,
      ];
    }
    else {
      return [
        'status' => '403',
        'data' => t('Access denied'),
      ];
    }
  }

  public function deleteComment($id) {
    $comment = $this->entityTypeManager->getStorage('comment')->load($id);
    if ($comment) {
      $access = $comment->access('delete', $this->currentUser);
      if ($access) {
        $comment->delete();
        return [
          'status' => '200',
          'data' => t('Comment deleted'),
        ];
      }
      else {
        return [
          'status' => '403',
          'data' => t('Access denied'),
        ];
      }
    }
    else {
      return [
        'status' => '404',
        'data' => t('Comment not found'),
      ];
    }
  }

  public function getComment($id) {
    $comment = $this->entityTypeManager->getStorage('comment')->load($id);
    if ($comment) {
      $access = $comment->access('view', $this->currentUser);
      if ($access) {
        return [
          'status' => '200',
          'data' => $comment,
        ];
      }
      else {
        return [
          'status' => '403',
          'data' => t('Access denied'),
        ];
      }
    }
    else {
      return [
        'status' => '404',
        'data' => t('Comment not found'),
      ];
    }
  }

  public function getComments($nid) {
    // get all comments for nid
    $node = $this->entityTypeManager->getStorage('node')->load($nid);
    // check node isset
    if(!$node){
      return [
        'status' => '404',
        'data' => t('Comments not found'),
      ];
    }
    $access = $node->access('view', $this->currentUser);
    $query = $this->entityTypeManager->getStorage('comment')->getQuery();
    $query->condition('entity_id', $nid);
    $query->condition('entity_type', 'node');
    $query->condition('status', 1);
    $query->sort('created', 'DESC');
    $cids = $query->execute();



    $comments = $this->entityTypeManager->getStorage('comment')->loadMultiple($cids);
    if ($access) {
      if (count($comments) > 0) {
        $com = [];
        foreach ($comments as $comment) {
          $com[] = $comment;
        }
        return [
          'status' => '200',
          'data' => $com,
        ];
      }
      else {
        return [
          'status' => '404',
          'data' => t('Comments not found'),
        ];
      }
    }else{
      return [
        'status' => '403',
        'data' => t('Access denied'),
      ];
    }
  }

  public function updateComment($id, $comment) {
    $comment = $this->entityTypeManager->getStorage('comment')->load($id);
    if ($comment) {
      $access = $comment->access('update', $this->currentUser);
      if ($access) {
        if (isset($comment['subject'])) {
          $comment->set('subject', $comment['subject']);
        }
        if (isset($comment['comment_body'])) {
          $comment->set('comment_body', $comment['comment_body']);
        }
        if (isset($comment['fields'])) {
          foreach ($comment['fields'] as $key => $value) {
            $comment->set($key, $value);
          }
        }
        $comment->save();
        return [
          'status' => '200',
          'data' => $comment,
        ];
      }
      else {
        return [
          'status' => '403',
          'data' => t('Access denied'),
        ];
      }
    }
    else {
      return [
        'status' => '404',
        'data' => t('Comment not found'),
      ];
    }
  }

}
