<?php
function check() {
  global $data, $db, $result;

  $sql = "select * from pet_test_user where session = '$data->session'";

  if (empty($user = $db->fetch($sql))) return true;
  return false;
}

function session() {
  global $data, $db, $result;

  $sql = "select * from pet_test_user where session = '$data->sess'";

  if (!empty($user = $db->fetch($sql))) {
    $admin = 0;
    if ($user['userid'] == 1 || $user['userid'] == 5) $admin = 1;

    $sql = "select * from pet_test_user";
    $list = $db->all($sql);

    $sql = "select username, concat(last_name, ' ', first_name) as fullname from pet_users where userid = $user[userid]";
    $userinfo = $db->fetch($sql);

    $result['status'] = 1;
    $result['config'] = array(
      'userid' => $user['userid'],
      'username' => $userinfo['username'],
      'fullname' => $userinfo['fullname'],
      'admin' => $admin,
      'session' => $data->sess,
      'users' => $list,
      'today' => date('d/m/Y'),
      'next' => date('d/m/Y', time() + 60 * 60 * 24 * 21),
      'module' => permission($user['userid'])
    );
  }
  return $result;
}

function login() {
  global $data, $db, $result;
  $username = mb_strtolower($data->username);
  $password = $data->password;

  include_once('Encryption.php');
  $sitekey = 'e3e052c73ae5aa678141d0b3084b9da4';
  $crypt = new NukeViet\Core\Encryption($sitekey);

  $sql = 'select userid, password from `pet_users` where LOWER(username) = "'. $username .'"';
  if (empty($user = $db->fetch($sql))) $result['messenger'] = 'Người dùng không tồn tại';
  else if (!$crypt->validate_password($password, $user['password'])) $result['messenger'] = 'Sai mật khẩu';
  else {
    $session = randomString();
    $admin = 0;
    if ($user['userid'] == 1 || $user['userid'] == 5) $admin = 1;

    $sql = "select * from pet_test_user";
    $list = $db->all($sql);

    $sql = "update pet_test_user set session = '$session'";
    $db->query($sql);

    $sql = "select username, concat(last_name, ' ', first_name) as fullname from pet_users where userid = $user[userid]";
    $userinfo = $db->fetch($sql);

    $result['status'] = 1;
    $result['config'] = array(
      'userid' => $user['userid'],
      'username' => $userinfo['username'],
      'fullname' => $userinfo['fullname'],
      'admin' => $admin,
      'session' => $session,
      'users' => $list,
      'today' => date('d/m/Y'),
      'next' => date('d/m/Y', time() + 60 * 60 * 24 * 21),
      'module' => permission($user['userid'])
    );
  }
  return $result;
}

function password() {
  global $data, $db, $result;
  include_once('Encryption.php');
  $sitekey = 'e3e052c73ae5aa678141d0b3084b9da4';
  $crypt = new NukeViet\Core\Encryption($sitekey);

  if (empty($data->old)) $result['messenger'] = 'Mật khẩu cũ trống';
  else if (empty($data->new)) $result['messenger'] = 'Mật khẩu mới trống';
  else {
    $userid = checkUserid();
    $sql = 'select password from `pet_users` where userid = '. $userid;
    $user_info = $db->fetch($sql);
    if (empty($user_info)) $result['messenger'] = 'Người dùng không tồn tại';
    else if (!$crypt->validate_password($data->old, $user_info['password'])) $result['messenger'] = 'Sai mật khẩu cũ';
    else {
      $password = $crypt->hash_password($data->new, '{SSHA512}');
      $sql = 'update `pet_users` set password = "'. $password .'" where userid = '. $userid;
      $db->query($sql);
      $result['status'] = 1;
      $result['messenger'] = 'Đã đổi mật khẩu';
    }
  }
  return $result;
}

function permission($userid) {
  global $data, $db;

  $module = array('work' => 0, 'kaizen' => 0, 'schedule' => 0, 'vaccine' => 0, 'spa' => 0, 'expire' => 0, 'blood' => 0, 'usg' => 0, 'drug' => 0, 'profile' => 0);
  $sql = "select * from pet_test_permission where userid = $userid";
  $query = $db->query($sql);
  
  while ($row = $query->fetch_assoc()) {
    $module[$row['module']] = intval($row['type']);
  }
  return $module;
}

function randomString($length = 10) {
  $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $charactersLength = strlen($characters);
  $randomString = '';
  for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[rand(0, $charactersLength - 1)];
  }
  return $randomString;
}