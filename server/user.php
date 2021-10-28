<?php
function check() {
  global $data, $db, $result;

  $sql = "select * from pet_test_user where session = '$data->session'";

  if (empty($user = $db->fetch($sql))) return true;
  return false;
}

function notify() {
  global $data, $db, $result;

  $userid = checkUserid();
  $sql = "update pet_test_notify set status = 1 where userid = $userid and status = 0";
  $db->query($sql);

  $result['status'] = 1;
  return $result;
}

function session() {
  global $data, $db, $result;

  $sql = "select * from pet_test_user where session = '$data->sess'";

  if (!empty($user = $db->fetch($sql))) {
    $result['status'] = 1;
    $result['data'] = getinitdata($user['userid']);
    $result['config'] = permission($user['userid']);
  }
  else {
    $result['messenger'] = "Phiên đăng nhập hết hạn";
  }
  return $result;
}

function getinitdata($userid) {
  global $db;
  $admin = 0;
  if ($userid == 1 || $userid == 5) $admin = 1;

  $sql = "select * from pet_test_user";
  $list = $db->all($sql);

  $sql = "select username, first_name, concat(last_name, ' ', first_name) as fullname from pet_users where userid = $userid";
  $userinfo = $db->fetch($sql);

  $sql = "select a.*, b.username from pet_test_doctor a inner join pet_users b on a.userid = b.userid";
  $doctor = $db->all($sql);

  $sql = "select * from pet_test_type where active = 1";
  $type = $db->all($sql);

  $sql = "select * from pet_test_notify where userid = $userid order by id desc limit 30";
  $notify = $db->all($sql);
  $result['notification'] = $notify;

  $sql = "select * from pet_test_notify where userid = $userid and status = 0 order by id desc limit 30";
  $notify = $db->count($sql);
  $result['notify'] = $notify;

  $lim = strtotime(date('Y/m/d')) + 60 * 60 * 24 * 3 - 1;
  $sql = "select * from pet_test_usg where status < 7 and recall < $lim and userid = $userid";
  $uc = $db->count($sql);
  $sql = "select * from pet_test_usg where status = 9 and userid = $userid";
  $ut = $db->count($sql);

  $sql = "select * from pet_test_vaccine where status < 2 and recall < $lim and userid = $userid";
  $vc = $db->count($sql);
  $sql = "select * from pet_test_vaccine where status = 5 and userid = $userid";
  $vt = $db->count($sql);

  $sql = "select id, name, value from pet_test_config where module = 'spa' order by id asc";
  $spa = $db->all($sql);
  $ds = array();

  foreach ($spa as $key => $s) {
    if ($s['value']) $ds []= $s['id'];
    $spa[$key]['check'] = 0;
  }

  $sql = "select * from pet_test_config where module = 'docs' and name = '$userid'";
  if (empty($docs = $db->fetch($sql))) {
    $sql = "insert into pet_test_config (module, name, value) values ('docs', $userid, '')";
    $db->query($sql);
    $docs['value'] = '';
  }
  $sql = "select * from pet_test_config where module = 'docscover' and name = '$userid'";
  if (empty($docscover = $db->fetch($sql))) {
    $sql = "insert into pet_test_config (module, name, value) values ('docscover', $userid, '')";
    $db->query($sql);
    $docscover['value'] = '';
  }
  if (!strlen($docs['value'])) $docs = array();
  else $docs = explode(', ', $docs['value']);


  return array(
    'userid' => $userid,
    'username' => $userinfo['username'],
    'name' => $userinfo['first_name'],
    'fullname' => $userinfo['fullname'],
    'admin' => $admin,
    'users' => $list,
    'doctor' => $doctor,
    'type' => $type,
    'spa' => $spa,
    'today' => date('d/m/Y'),
    'next' => date('d/m/Y', time() + 60 * 60 * 24 * 21),
    'usg' => array('c' => $uc, 't' => $ut),
    'vaccine' => array('c' => $vc, 't' => $vt),
    'default' => array(
      'spa' => $ds,
      'docs' => $docs,
      'docscover' => $docscover['value']
    )
  );
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
    $sql = "update pet_test_user set session = '$session' where userid = $user[userid]";
    $db->query($sql);
    $result['status'] = 1;
    $result['session'] = $session;
    $result['data'] = getinitdata($user['userid']);
    $result['config'] = permission($user['userid']);
  }
  return $result;
}

function signin() {
  global $data, $db, $result;
  $username = mb_strtolower($data->username);
  $password = $data->password;

  include_once('Encryption.php');
  $sitekey = 'e3e052c73ae5aa678141d0b3084b9da4';
  $crypt = new NukeViet\Core\Encryption($sitekey);

  $sql = 'select userid, password from `pet_users` where LOWER(username) = "'. $username .'"';
  if (!empty($user = $db->fetch($sql))) $result['messenger'] = 'Tên người dùng đã tồn tại';
  else {
    $time = time();
    $sql = "insert into pet_users (username, first_name, last_name, password, email, photo, regdate) values ('$data->username', '$data->firstname', '$data->lastname', '". $crypt->hash_password($data->password) ."', '', '', $time)";
    $userid = $db->insertid($sql);
    
    $session = randomString();
    $sql = "update pet_test_user set session = '$session' where userid = $userid";
    $db->query($sql);

    $result['status'] = 1;
    $result['session'] = $session;
    $result['data'] = getinitdata($userid);
    $result['config'] = permission($userid);
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

  $sql = "select name, 0 as per from pet_test_config where module = 'setting'";
  $c = $db->obj($sql, 'name', 'per');

  $sql = "select * from pet_test_user_per where userid = $userid";
  $query = $db->query($sql);
  
  while ($row = $query->fetch_assoc()) {
    $c[$row['module']] = intval($row['type']);
  }
  return $c;
}
