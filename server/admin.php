<?php

function auto() {
  global $db, $data, $result;
  
  $result['status'] = 1;
  $result['list'] = getList();
  return $result;
}

function filter() {
  global $db, $data, $result;
  
  $result['status'] = 1;
  $result['list'] = filterUser();
  return $result;
}

function doctor() {
  global $db, $data, $result;

  $sql = "select * from pet_test_user_per where userid = $data->userid and module = 'doctor'";
  if (empty($p = $db->fetch($sql))){
    $sql = "insert into pet_test_user_per (userid, module, type) values ($data->userid, 'doctor', 1)";
    $db->query($sql);
  }
  else {
    $sql = "update pet_test_user_per set type = ". intval(!$p['type']) ." where id = $p[id]";
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['config'] = getConfig();
  $result['list'] = getList();
  return $result;
}

function manager() {
  global $db, $data, $result;

  $sql = "select * from pet_test_user_per where userid = $data->userid and module = 'manager'";
  if (empty($p = $db->fetch($sql))){
    $sql = "insert into pet_test_user_per (userid, module, type) values ($data->userid, 'manager', 1)";
    $db->query($sql);
  }
  else {
    $sql = "update pet_test_user_per set type = ". intval(!$p['type']) ." where id = $p[id]";
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['config'] = getConfig();
  $result['list'] = getList();
  return $result;
}

function admin() {
  global $db, $data, $result;

  $sql = "select * from pet_test_user_per where userid = $data->userid and module = 'admin'";
  if (empty($p = $db->fetch($sql))){
    $sql = "insert into pet_test_user_per (userid, module, type) values ($data->userid, 'admin', 1)";
    $db->query($sql);
  }
  else {
    $sql = "update pet_test_user_per set type = ". intval(!$p['type']) ." where id = $p[id]";
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['config'] = getConfig();
  $result['list'] = getList();
  return $result;
}

function insert() {
  global $db, $data, $result;

  $sql = "update pet_users set active = 1 where userid = $data->userid";
  $db->query($sql);
  $result['status'] = 1;
  $result['list'] = filterUser();
  $result['admin'] = getList();
  $result['messenger'] = 'Đã thêm nhân viên';
  
  return $result;
}

function update() {
  global $db, $data, $result;
  
  $sql = "update pet_users set fullname = '$data->fullname', name = '$data->name' where userid = $data->userid";
  $db->query($sql);

  foreach ($data->module as $name => $value) {
    $sql = "select * from pet_test_user_per where module = '$name' and userid = $data->userid";
    $userinfo = $db->fetch($sql);
  
    if (empty($userinfo)) {
      $sql = "insert into pet_test_user_per (userid, module, type) values ($data->userid, '$name', '$value')";
    }
    else {
      $sql = "update pet_test_user_per set type = '$value' where module = '$name' and userid = $data->userid";
    }
    $db->query($sql);
  }
    
  $result['status'] = 1;
  $result['list'] = getList();
  $result['config'] = getConfig();
  return $result;
}

function remove() {
  global $db, $data, $result;
  
  $sql = "update pet_users set active = 0 where userid = $data->userid";
  $db->query($sql);
    
  $result['status'] = 1;
  $result['list'] = getList();
  return $result;
}

function getList() {
  global $db;

  $module = getPer();

  $sql = 'select name, username, fullname, userid from pet_users where active = 1';
  $list = $db->all($sql);
  
  foreach ($list as $index => $row) {
    $sql = 'select * from pet_test_user_per where userid = '. $row['userid'];
    $query = $db->query($sql);
    $temp = $module;
    while ($info = $query->fetch_assoc()) {
      $temp[$info['module']] = $info['type'];
    }
    $list[$index]['module'] = $temp;
  }
  return $list;
}

function getPer() {
  global $db;

  $sql = "select name, 0 as per from pet_test_config where module = 'setting'";
  $c = $db->obj($sql, 'name', 'per');
  return $c;
}

function getConfig() {
  global $db;
  
  $userid = checkUserid();
  $sql = 'select * from pet_test_user_per where userid = '. $userid;
  return $db->obj($sql, 'module', 'type');
}

function filterUser() {
  global $db, $data;
  
  $sql = 'select userid from pet_users';
  $list = $db->obj($sql, 'userid', 'userid');
  
  $sql = "select userid, fullname, username from pet_users where active = 0 and (username like '%$data->key%' or fullname like '%$data->key%')";

  return $db->all($sql);
}