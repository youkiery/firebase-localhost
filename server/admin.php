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

function insert() {
  global $db, $data, $result;

  $sql = 'select * from pet_test_user where userid = '. $data->id;
  $userinfo = $db->fetch($sql);
  
  if (!empty($userinfo)) {
    $result['messenger'] = 'Nhân viên đã tồn tại';
  }
  else {
    $sql = "insert into pet_test_user (userid, manager, `except`, daily, kaizen) values($data->id, 0, 0, 0, 0)";
    $db->query($sql);

    $result['status'] = 1;
    $result['list'] = filterUser();
    $result['messenger'] = 'Đã thêm nhân viên';
  }
  
  return $result;
}

function update() {
  global $db, $data, $result;
  
  foreach ($data->module as $name => $value) {
    $sql = "select * from pet_test_user_per where module = '$name' and userid = $data->id";
    $userinfo = $db->fetch($sql);
  
    if (empty($userinfo)) {
      $sql = "insert into pet_test_user_per (userid, module, type) values ($data->id, '$name', '$value')";
    }
    else {
      $sql = "update pet_test_user_per set type = '$value' where module = '$name' and userid = $data->id";
    }
    $db->query($sql);
  }
    
  $result['status'] = 1;
  $result['data'] = auto();
  $result['config'] = getConfig();
  return $result;
}

function remove() {
  global $db, $data, $result;
  
  $sql = 'delete from pet_test_user where userid = '. $data->id;
  $query = $db->query($sql);
    
  $result['status'] = 1;
  $result['list'] = getList();
  return $result;
}

function getList() {
  global $db;

  $module = getPer();

  $sql = 'select username, concat(a.last_name, " ", a.first_name) as fullname, a.userid from pet_users a inner join pet_test_user b on a.userid = b.userid';
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
  
  $sql = 'select userid from pet_test_user';
  $list = $db->obj($sql, 'userid', 'userid');
  
  $sql = "select userid, concat(last_name, ' ', first_name) as fullname, username from pet_users where userid not in (". implode(', ', $list) .") and (username like '%$data->key%' or first_name like '%$data->key%' or last_name like '%$data->key%')";

  return $db->all($sql);
}