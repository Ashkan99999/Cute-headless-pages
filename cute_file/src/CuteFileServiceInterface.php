<?php

namespace Drupal\cute_file;

/**
 * Interface CuteFileServiceInterface.
 */
interface CuteFileServiceInterface {

  public function getFile($fid);

  public function deleteFile($fid);
}
