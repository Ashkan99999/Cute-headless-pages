<?php

namespace Drupal\cute_user;

/**
 * Interface UserServiceInterface.
 */
interface UserServiceInterface {

  public function getUser($uid);

  public function getUserName($uid);

  // get all user response uid and user name
  public function getAllUser();

  // get all user response name username date created and uid
  public function getAllUserData();

  public function createUser($data);

  public function deleteUser($uid);

  public function updateUser($id, $data);

  public function login($username, $password);

  public function userRegister($data);

  public function getCurrentUser();

  public function getAllPermission($uid);

  public function FilterUser($data);

  public function sendCode($data);

  public function checkCode($mobileNumber, $code);

  public function resetPassword($data);

  public function registerByEmail($data); // register by email


}
