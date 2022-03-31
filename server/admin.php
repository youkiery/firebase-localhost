<?php

function auto() {
  global $db, $data, $result;
  
  $result['status'] = 1;
  $result['list'] = getList();
  return $result;
}

function reduce() {
  global $db, $data, $result;

  $sql = "update pet_test_vaccine set status = 4 where calltime < $data->date and status < 3";
  $db->query($sql);
  $sql = "update pet_test_usg set status = 8 where calltime < $data->date and status < 7";
  $db->query($sql);

  $result['status'] = 1;
  return $result;
}

function recycle() {
  global $db, $data, $result;

  // foreach data->option
  // lấy danh sách data->doctor + db(user not in doctor), chuyển cho danh sách db(doctor - data->doctor)
  $sql = "select userid from pet_users where userid not in (select userid from pet_test_user_per where module = 'doctor' and type = 1)";
  $doctor = array_merge($data->doctor, $db->arr($sql, 'userid'));
  $sql = "select userid from pet_test_user_per where module = 'doctor' and type = 1 and userid not in (". implode(', ', $data->doctor) .")";
  $target = $db->arr($sql, 'userid');

  if (in_array('vaccine', $data->option) !== false) {
    $sql = "select a.id, b.name from pet_test_vaccine a inner join pet_users b on a.userid = b.userid where (a.status < 3 or a.status = 5) and a.userid in (". implode(', ', $doctor) .")";
    $list = $db->all($sql);

    $l = count($list);
    $d = count($target);
    $n = (int) ($l / $d);

    $c = 0;
    for ($i = 0; $i < $l; $i++) { 
      if ($c < ($d - 1) && $i >= ($c + 1) * $n) $c ++;
      $sql = "update pet_test_vaccine set userid = $target[$c] where id = ". $list[$i]['id'];
      $db->query($sql);
    }
  }

  if (in_array('vaccine', $data->option) !== false) {
    $sql = "select a.id, b.name from pet_test_usg a inner join pet_users b on a.userid = b.userid where (a.status < 7 or a.status = 9) and a.userid in (". implode(', ', $doctor) .")";
    $list = $db->all($sql);

    $l = count($list);
    $d = count($target);
    $n = (int) ($l / $d);

    $c = 0;
    for ($i = 0; $i < $l; $i++) { 
      if ($c < ($d - 1) && $i >= ($c + 1) * $n) $c ++;
      $sql = "update pet_test_usg set userid = $target[$c] where id = ". $list[$i]['id'];
      $db->query($sql);
    }
  }

  $result['status'] = 1;
  return $result;
}

function vaccine() {
  global $db, $data, $result;
  
  $sql = "select * from pet_test_config where name = 'vaccine-comma'";
  if (empty($c = $db->fetch($sql))) {
    $sql = "insert into pet_test_config (module, name, value) values ('vaccine', 'vaccine-comma', ';')";
    $db->query($sql);
    $c['value'] = '-';
  }

  $result['status'] = 1;
  $result['code'] = $c['value'];
  return $result;
}

function savevaccine() {
  global $db, $data, $result;
  
  $sql = "update pet_test_config set value = '$data->comma' where name = 'vaccine-comma'";
  $db->query($sql);

  $result['status'] = 1;
  return $result;
}

function spa() {
  global $db, $data, $result;
  $sql = "select id, name, value, alt from pet_test_config where module = 'spa' order by value asc";
  $result['status'] = 1;
  $result['list'] = $db->all($sql);
  return $result;
}

function type() {
  global $db, $data, $result;
  $sql = "select * from pet_test_type where active = 1";
  $result['status'] = 1;
  $result['list'] = $db->all($sql);
  return $result;
}

function usg() {
  global $db, $data, $result;
  $sql = "select id, name from pet_test_config where module = 'usg'";
  $result['status'] = 1;
  $result['list'] = $db->all($sql);
  return $result;
}

function filter() {
  global $db, $data, $result;
  
  $result['status'] = 1;
  $result['list'] = filterUser();
  return $result;
}

function toggle() {
  global $db, $data, $result;

  $sql = "select * from pet_test_user_per where userid = $data->userid and module = '$data->per'";
  if (empty($p = $db->fetch($sql))){
    $sql = "insert into pet_test_user_per (userid, module, type) values ($data->userid, '$data->per', 1)";
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

function change() {
  global $db, $data, $result;

  $reversal = array(0 => 1, 2, 0);
  $sql = "select * from pet_test_user_per where userid = $data->userid and module = '$data->per'";
  if (empty($p = $db->fetch($sql))) {
    $sql = "insert into pet_test_user_per (userid, module, type) values ($data->userid, '$data->per', 1)";
    $db->query($sql);
  }
  else {
    $rev = $reversal[$p['type']];
    $sql = "update pet_test_user_per set type = $rev where id = $p[id]";
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

  $sql = "update pet_test_user_per set type = 0 where userid = $data->userid";
  $db->query($sql);
    
  $result['status'] = 1;
  $result['list'] = getList();
  return $result;
}

function getList() {
  global $db;

  $module = array(
    'spa' => 0,
    'vaccine' => 0,
    'schedule' => 0,
    'item' => 0,
    'kaizen' => 0,
    'drug' => 0,
    'price' => 0,
    'ride' => 0,
    'profile' => 0,
    'physical' => 0,
    'his' => 0,
    'cart' => 0,
    'transport' => 0,
  );

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
  
  $userid = checkuserid();
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