<?php

namespace Drupal\cute_user;

use Drupal;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * Class UserService.
 */
class UserService implements UserServiceInterface {

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
   * Constructs a new UserService object.
   */
  public function __construct(
    AccountProxyInterface      $current_user,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
  }


  /**
   * @param $data
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createUser($data) {
    $user = $this->entityTypeManager->getStorage('user')->create($data);
    $access = $this->checkAccess($user, 'create');
    if ($access) {
      $user->save();
      return ["status" => 200, "data" => $user->toArray()];
    }
    return ["status" => 403, "data" => "Access denied"];
  }

  /**
   * @param $user
   * @param $operation
   *
   * @return bool
   */
  private function checkAccess($user, $operation) {
    return $user->access($operation, $this->currentUser);
  }

  /**
   * @param $uid
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function deleteUser($uid) {
    $user = Drupal::entityTypeManager()->getStorage('user')->load($uid);
    if ($user) {
      $access = $this->checkAccess($user, 'delete');
      if ($access) {
        // load all content of user
        $query = Drupal::entityQuery('node')
          ->condition('uid', $uid);
        $nids = $query->execute();
        $nodes = Drupal::entityTypeManager()
          ->getStorage('node')
          ->loadMultiple($nids);
        // delete all content of user
        foreach ($nodes as $node) {
          $node->delete();
        }
        $user->delete();
        return ["status" => 204, "data" => "User deleted"];
      }
      return ["status" => 403, "data" => "Access denied"];
    }
    return ["status" => 404, "data" => "User not found"];

  }

  /**
   * @param $uid
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getUser($uid) {
    // get user by uid
    $user = Drupal::entityTypeManager()->getStorage('user')->load($uid);
    if ($user) {
      $access = $this->checkAccess($user, 'view');
      if ($access) {
        return ["status" => 200, "data" => $user->toArray()];
      }
      return ["status" => 403, "data" => "Access denied"];
    }
    return ["status" => 404, "data" => "User not found"];
  }

  /**
   * @param $uid
   *
   * @return array|void
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getUserName($uid) {
    $user = Drupal::entityTypeManager()->getStorage('user')->load($uid);
    if ($user) {
      $access = $this->checkAccess($user, 'view');
      $userNae = $user->realname;
      if ($access) {
        return ["status" => 200, "data" => $userNae];
      }
      return ["status" => 403, "data" => "Access denied"];
    }
  }

  /**
   * @param $data
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function updateUser($id, $data) {
    // update user by uid
    $user = Drupal::entityTypeManager()->getStorage('user')->load($id);
    if ($user) {
      $access = $this->checkAccess($user, 'update');
      if ($access) {
        foreach ($data as $key => $value) {
          $user->set($key, $value);
        }
        $user->save();
        return ["status" => 200, "data" => $user->toArray()];
      }
      return ["status" => 403, "data" => "Access denied"];
    }
    return ["status" => 404, "data" => "User not found"];
  }


  /**
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getAllUser() {
    // get all users
    $users = Drupal::entityTypeManager()->getStorage('user')->loadMultiple();


    $result = [];
    foreach ($users as $user) {
      if ($user->id() != 0) {
        $result[] = [
          'id' => $user->id(),
          'name' => $user->realname ? $user->realname : $user->get('name')
            ->getString(),
        ];
      }

    }
    return ["status" => 200, "data" => $result];
  }

  /**
   * @param $username
   * @param $password
   */
  public function login($username, $password) {
  }


  /**
   * @return array
   */
  public function getCurrentUser() {
    //load current user
    $user = Drupal\user\Entity\User::load(Drupal::currentUser()->id());

    $role = $user->getRoles();
    $uid = $user->id();
    $username = $user->getAccountName();
    $email = $user->getEmail();
    $realName = $user->realname ? $user->realname : $username;
    if ($user->get('user_picture')->target_id) {
      $pictureUri = $user->user_picture->entity->getFileUri();
      $style = Drupal::entityTypeManager()
        ->getStorage('image_style')
        ->load('thumbnail');
      $urlPicture = $style->buildUrl($pictureUri);
    }
    $result = [
      'id' => $uid,
      'username' => $username,
      'email' => $email,
      'fullName' => $realName,
      'role' => $role,
      'userPicture' => $urlPicture ? $urlPicture : '',
    ];
    return ["status" => 200, "data" => $result];
  }

  /**
   * @param $uid
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getAllPermission($uid) {
    // get all permission by uid
    $user = Drupal::entityTypeManager()->getStorage('user')->load($uid);
    $user_roles = $user->getRoles();
    //get permission by user
    $permissions = [];
    foreach ($user_roles as $role) {
      $permissions = array_merge($permissions, user_role_permissions([$role]));
    }
    return ["status" => 200, "data" => $permissions];
  }

  /**
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getAllUserData() {
    $user = Drupal::entityTypeManager()->getStorage('user')->loadMultiple();
    $result = [];
    foreach ($user as $item) {
      // skip admin user and anonymous user
      if ($item->id() != 0 && $item->id() != 1) {
        $result[] = [
          'uid' => $item->id(),
          'name' => $item->realname ? $item->realname : $item->get('name')
            ->getString(),
          'username' => $item->get('name')->getString(),
          'email' => $item->getEmail(),
          'role' => $item->getRoles(),
          'status' => $item->isBlocked() ? '0' : '1',
          'lastLogin' => $item->getLastLoginTime(),
          'created' => $item->getCreatedTime(),
        ];
      }
    }
    return ["status" => 200, "data" => $result];
  }

  /**
   * @param $data
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function FilterUser($data) {
    $query = $this->entityTypeManager->getStorage('user')->getQuery();
    if (isset($data['field'])) {
      foreach ($data['field'] as $key => $value) {
        $query->condition($key, $value['value'], $value['operator']);
      }
    }
    $uids = $query->execute();
    $users = $this->entityTypeManager->getStorage('user')->loadMultiple($uids);
    $result = [];
    foreach ($users as $user) {
      $result[] = $user->toArray();
    }
    if (count($result) > 0) {
      return ["status" => 200, "data" => $result];
    }
    return ["status" => 404, "data" => "User not found"];
  }

  public function sendCode($data) {

    $mobileNumber = $data['mobileNumber'];

    if (!$mobileNumber) {
      return [
        "status" => 201,
        "data" => t("Please enter your mobile number"),
      ];
    }
    // validate mobile number
    if (!preg_match('/^09[0-9]{9}$/', $mobileNumber)) {
      return [
        "status" => 201,
        "data" => t("Please enter a valid mobile number"),
      ];
    }
    $code = rand(1000, 9999);

    $cuteTempStore = Drupal::service('cute_user.user_storage')
      ->get('cute_user');
    $checkMobile = $cuteTempStore->get($mobileNumber);

    if ($checkMobile) {
      return [
        "status" => 201,
        "data" => t("The code $checkMobile has already been sent to your mobile number"),
      ];
    }

    // save code in private temp store
    $cuteTempStore->set($mobileNumber, $code);

    return [
      "status" => 201,
      "data" => t("Code $code has been sent to $mobileNumber"),
    ];
  }

  /**
   * @param $data
   */
  public function userRegister($data) {

    $getData = [
      'name' => $data['name'],
      'pass' => $data['pass'],
      'status' => 1,
    ];
    if (isset($data['mail'])) {
      $getData['mail'] = $data['mail'];
    }

    if (isset($data['roles'])) {
      $getData['roles'] = $data['roles'];
    }
    if (isset($data['fields'])) {
      foreach ($data['fields'] as $key => $value) {
        $getData[$key] = $value;
      }
    }

    if (!isset($data['name'])) {
      return ["status" => 400, "data" => t('Name is required')];
    }
    if (!isset($data['pass'])) {
      return ["status" => 400, "data" => t('Password is required')];
    }

    // check code

    $code = $this->checkCode($data['name'], $data['code']);

    if ($code) {
      $checkUser = $this->entityTypeManager->getStorage('user')
        ->loadByProperties(['name' => $getData['name']]);
      if ($checkUser) {
        return [
          "status" => 400,
          "data" => t('User already exists please try another name or login'),
        ];
      }


      $user = $this->entityTypeManager->getStorage('user')->create($getData);
      $user->save();

      return ["status" => 200, "data" => $user->toArray()];
    }
    else {
      return ["status" => 400, "data" => t('bad request')];
    }
  }

  public function resetPassword($data) {
    // check code

    $code = $this->checkCode($data['name'], $data['code']);
    if (!$code) {
      return ["status" => 400, "data" => t('bad request')];
    }

    $user = $this->entityTypeManager->getStorage('user')
      ->loadByProperties(['name' => $data['name']]);
    if ($user) {
      $user = reset($user);
      $user->setPassword($data['pass']);
      $user->save();
      return ["status" => 200, "data" => t('Password changed successfully')];
    }
    return ["status" => 400, "data" => t('User not found')];
  }

  public function checkCode($mobileNumber, $code) {
    // check code
    $cuteTempStore = Drupal::service('cute_user.user_storage')
      ->get('cute_user');

    $checkMobile = $cuteTempStore->get($mobileNumber);

    if ($checkMobile) {
      if ($checkMobile == $code) {
        return TRUE;
      }
      return FALSE;
    }
    return FALSE;
  }

  public function registerByEmail($data)
  {
    if (!isset($data['name'])) {
      return ["status" => 400, "data" => t('Name is required')];
    }
    if (!isset($data['pass'])) {
      return ["status" => 400, "data" => t('Password is required')];
    }
    if (!isset($data['mail'])) {
      return ["status" => 400, "data" => t('Email is required')];
    }
    // regular expression for email
    $email = $data['mail'];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      return ["status" => 400, "data" => t('Email is not valid')];
    }
    $checkUser = $this->entityTypeManager->getStorage('user')
      ->loadByProperties(['name' => $data['name']]);
    if ($checkUser) {
      return [
        "status" => 400,
        "data" => t('User already exists please try another name or login'),
      ];

    }
    $getData = [
      'name' => $data['name'],
      'pass' => $data['pass'],
      'status' => 1,
      'mail' => $data['mail'],
    ];
    if (isset($data['roles'])) {
      $getData['roles'] = $data['roles'];
    }
    if (isset($data['fields'])) {
      foreach ($data['fields'] as $key => $value) {
        $getData[$key] = $value;
      }
    }
    $user = $this->entityTypeManager->getStorage('user')->create($getData);
    $user->save();

    return ["status" => 200, "data" => $user->toArray()];
  }


}
