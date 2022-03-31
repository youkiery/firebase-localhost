<?php
function check() {
  global $data, $db, $result;

  $sql = "select * from pet_users where session = '$data->session'";

  if (empty($user = $db->fetch($sql))) return true;
  return false;
}

function notify() {
  global $data, $db, $result;

  $userid = checkuserid();
  $sql = "update pet_test_notify set status = 1 where userid = $userid and status = 0";
  $db->query($sql);

  $result['status'] = 1;
  return $result;
}

function session() {
  global $data, $db, $result;

  $sql = "select * from pet_test_session where session = '$data->sess'";
  $user = $db->fetch($sql);
  $time = time();
  $end = $time - 60 * 60 * 24 * 7; // 7 days

  if (!empty($user)) {
    if ($user['time'] > $end) {
      $result['status'] = 1;
      $sql = "update pet_test_session set time = $time where id = $user[id]";
      $db->query($sql);
      $result['data'] = getinitdata($user['userid']);
      $result['config'] = permission($user['userid']);
    }
    else {
      $sql = "delete from pet_test_session where id = $user[id]";
      $db->query($sql);
      $result['messenger'] = "Phiên đăng nhập hết hạn";
    }
  }
  else {
    $result['messenger'] = "Phiên đăng nhập hết hạn";
  }
  return $result;
}

function getHisChatCount($userid) {
  global $db;
  
  $sql = "select * from pet_test_xray where doctorid = $userid or pos = 1";
  $c = $db->all($sql);

  $count = 0;
  foreach ($c as $row) {
    $sql = "select * from pet_test_xray_read where side = 0 and userid = $userid and postid = $row[id]";
    if (empty($r = $db->fetch($sql))) {
      $sql = "insert into pet_test_xray_read (side, userid, postid, time) values(0, $userid, $row[id], 0)";
      $db->query($sql);
      $r = array('time' => 0);
    }

    $sql = "select id from pet_test_xray_chat where postid = $row[id] and side = 1 and time > $r[time]";
    $count += intval($db->count($sql));
  }
  
  return $count;
}

function getinitdata($userid) {
  global $db;
  $admin = 0;
  if ($userid == 1 || $userid == 5) $admin = 1;
  else {
    $sql = "select * from pet_test_user_per where userid = $userid and module = 'admin' and type = 1";
    if (!empty($db->fetch($sql))) $admin = 1;
  }

  $sql = "select username, name, fullname from pet_users where userid = $userid";
  $userinfo = $db->fetch($sql);

  $sql = "select userid, name, fullname, username from pet_users where userid in (select userid from pet_test_user_per where module = 'doctor' and type = 1)";
  $doctor = $db->all($sql);

  $sql = "select * from pet_test_type where active = 1";
  $type = $db->all($sql);

  $lim = strtotime(date('Y/m/d')) + 60 * 60 * 24 * 3 - 1;
  $sql = "select * from pet_test_usg where status < 7 and recall < $lim and userid = $userid";
  $uc = $db->count($sql);
  $sql = "select * from pet_test_usg where status = 9 and userid = $userid";
  $ut = $db->count($sql);

  $sql = "select * from pet_test_vaccine where status < 2 and recall < $lim and userid = $userid";
  $vc = $db->count($sql);
  $sql = "select * from pet_test_vaccine where status = 5 and userid = $userid";
  $vt = $db->count($sql);

  $sql = "select id, name, value, alt from pet_test_config where module = 'spa' order by value asc";
  $spa = $db->all($sql);
  $ds = array();

  foreach ($spa as $key => $s) {
    if ($s['alt']) $ds []= $s['id'];
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

  $sql = "select id, name from pet_test_config where module = 'usg'";
  $usgcode = $db->all($sql);

  return array(
    'month' => array('start' => date('Y-m-01'), 'end' => date('Y-m-t')),
    'userid' => $userid,
    'username' => $userinfo['username'],
    'name' => $userinfo['name'],
    'fullname' => $userinfo['fullname'],
    'admin' => $admin,
    'doctor' => $doctor,
    'type' => $type,
    'spa' => $spa,
    'his' => getHisChatCount($userid),
    'usgcode' => $usgcode,
    'today' => date('d/m/Y'),
    'next' => date('d/m/Y', time() + 60 * 60 * 24 * 21),
    'usg' => $uc + $ut,
    'vaccine' => $vc + $vt,
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
    $time = time();
    $sql = "insert into pet_test_session (userid, session, time) values($user[userid], '$session', $time)";
    $db->query($sql);
    $result['status'] = 1;
    $result['session'] = $session;
    $result['data'] = getinitdata($user['userid']);
    $result['config'] = permission($user['userid']);
  }
  return $result;
}

function logout() {
  global $data, $db, $result;

  $sql = "delete from pet_test_session where session = '$data->session'";
  $db->query($sql);

  $result['status'] = 1;
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
    $sql = "insert into pet_users (username, name, fullname, password, photo, regdate) values ('$data->username', '$data->name', '$data->fullname', '". $crypt->hash_password($data->password) ."', '', $time)";
    $userid = $db->insertid($sql);
    
    $session = randomString();
    $time = time();
    $sql = "insert into pet_test_session (userid, session, time) values($userid, '$session', $time)";
    $db->query($sql);

    $result['status'] = 1;
    $result['session'] = $session;
    $result['data'] = getinitdata($userid);
    $result['config'] = permission($userid);
  }
  return $result;
}

function changename() {
  global $data, $db, $result;

  $userid = checkuserid();
  $sql = "update pet_users set name = '$data->name' where userid = $userid";
  $db->query($sql);

  $result['status'] = 1;
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
    $userid = checkuserid();
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
