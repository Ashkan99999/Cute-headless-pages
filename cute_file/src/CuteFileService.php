<?php

namespace Drupal\cute_file;

use Drupal;
use Drupal\core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * Class CuteFileService.
 */
class CuteFileService implements CuteFileServiceInterface {

  /**
   * Drupal\Core\File\FileSystemInterface definition.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;


  /**
   * current user
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;


  /**
   * Drupal\Core\File\FileUrlGeneratorInterface definition.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  protected $entityTypeManager;

  /**
   * Constructs a new CuteFileService object.
   */
  public function __construct(FileSystemInterface $file_system, FileUrlGeneratorInterface $file_url_generator, EntityTypeManagerInterface $entity_type_manager, AccountProxyInterface $current_user) {
    $this->fileSystem = $file_system;
    $this->fileUrlGenerator = $file_url_generator;
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
  }

  public function getFile($fid) {
    $file = $this->entityTypeManager->getStorage('file')->load($fid);
    if ($file) {
      $base_url = Drupal::request()->getSchemeAndHttpHost();
      $url = [
        'fid' => $file->id(),
        'filename' => $file->getFilename(),
        'uri' => $file->getFileUri(),
        'url' => $base_url . $file->get('uri')->url,
        'size' => $file->getSize(),
        'mime' => $file->getMimeType(),
        'created' => $file->getCreatedTime(),
        'changed' => $file->getChangedTime(),
      ];
      return [
        'data' => $url,
        'status' => 200,
      ];
    }
    else {
      return [
        'message' => t('File not found'),
        'status' => 404,
      ];
    }
  }


  public function deleteFile($fid) {
    $file = $this->entityTypeManager->getStorage('file')->load($fid);
    if ($file) {
      $access = FALSE;
      $role = $this->currentUser->getRoles();
      if ($this->currentUser->id() == $file->getOwnerId()) {
        $access = TRUE;
      }
      if (in_array('administrator', $role)) {
        $access = TRUE;
      }
      if (in_array('admin', $role)) {
        $access = TRUE;
      }
      if ($access) {
        $file->delete();
        return 204;
      }
      else {
        return 403;
      }
    }
    else {
      return 404;
    }
  }

}
