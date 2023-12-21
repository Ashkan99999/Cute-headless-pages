<?php

namespace Drupal\cute_paragraphs;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Class ParagraphsService.
 */
class ParagraphsService implements ParagraphsServiceInterface {

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
   * Constructs a new ParagraphsService object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, AccountProxyInterface $current_user) {
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
  }

  public function create($data) {

    $access =  $this->entityTypeManager->getAccessControlHandler('paragraph')->createAccess($data['type'], $this->currentUser);

   if ($access) {
      $paragraph = Paragraph::create($data);
      $paragraph->save();
      return [
        'status' => 200,
        'data' => $paragraph->toArray(),
      ];
    }
      return [
        'status' => 403,
        'message' => 'Access denied',
      ];

  }

  private function checkAccess($paragraph, $operation) {
    return $paragraph->access($operation, $this->currentUser);
  }

  public function delete($rid) {
    $paragraph = $this->entityTypeManager->getStorage('paragraph')->load($rid);
    if ($paragraph) {
      $access = $this->checkAccess($paragraph, 'delete');
      if ($access) {
        $paragraph->delete();
        return ["status" => 200, "data" => TRUE];
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
      "message" => t('Paragraph not found.'),
    ];
  }

  public function get($rid) {
    $paragraph = $this->entityTypeManager->getStorage('paragraph')->load($rid);
    if ($paragraph) {
      $access = $this->checkAccess($paragraph, 'view');
      if ($access) {
        return ["status" => 200, "data" => $paragraph->toArray()];
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
      "message" => t('Paragraph not found.'),
    ];
  }

  public function update($rid, $datas) {
    $paragraph = $this->entityTypeManager->getStorage('paragraph')->load($rid);
    if ($paragraph) {
      $access = $this->checkAccess($paragraph, 'update');
      if ($access) {
        foreach ($datas as $field_name => $value) {
          $paragraph->set($field_name, $value);
        }
        $paragraph->save();
        return ["status" => 200, "data" => $paragraph->toArray()];
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
      "message" => t('Paragraph not found.'),
    ];
  }

}
