<?php
function filter() {
  global $data, $db, $result;
  $result['status'] = 1;
  $result['time'] = time();
  $result['list'] = getList();

  return $result;
}

function init() {
  global $data, $db, $result;

  $userid = checkUserid();

  $sql = "select * from pet_test_user_per where module = 'spa' and type = 2 and userid = $userid";
  if (!empty($db->fetch($sql))) {
    $sql = "select * from pet_test_doctor";
    $result['doctor'] = $db->all($sql);
  }
  else $result['doctor'] = array();

  $result['status'] = 1;
  $result['time'] = time();
  $result['type'] = gettypeobj();
  $result['list'] = getList();
  return $result;
}

function auto() {
  global $data, $db, $result;

  $data->ctime = $data->ctime / 1000;
  $today = date('d/m/Y');
  $ctime = date('d/m/Y', $data->ctime);
  
  if ($today !== $ctime) {
    $result['status'] = 1;
    $result['list'] = getList();
  }
  else {
    $sql = "select id from pet_test_spa where utime > $data->ctime";
    $result['status'] = 1;
  
    if (!empty($db->fetch($sql))) {
      $result['time'] = time();
      $result['list'] = getList();
    }
  }
  
  if (empty($result['list'])) $result['list']= array();

  return $result;
}

function remove() {
  global $data, $db, $result;

  $sql = "delete from pet_test_spa where id = $data->id";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['time'] = time();
  $result['list'] = getList();
  
  return $result;
}

function done() {
  global $data, $db, $result;

  if (!empty($data->uid)) $sql = "update pet_test_spa set utime = ". time() .", duser = $data->uid, status = 1 where id = $data->id";
  else {
    $sql = "select * from pet_test_spa where id = $data->id";
    $s = $db->fetch($sql);
    if (!$s['duser']) {
      $userid = checkUserid();
      $sql = "update pet_test_spa set utime = ". time() .", status = 1, duser = $userid where id = $data->id";
    }
    else $sql = "update pet_test_spa set utime = ". time() .", status = 1 where id = $data->id";
  } 
  $db->query($sql);
  
  $result['status'] = 1;
  $result['time'] = time();
  $result['list'] = getList();
  
  return $result;
}

function called() {
  global $data, $db, $result;

  if (!empty($data->uid)) $sql = "update pet_test_spa set utime = ". time() .", duser = $data->uid, status = 2 where id = $data->id";
  else {
    $sql = "select * from pet_test_spa where id = $data->id";
    $s = $db->fetch($sql);
    if (!$s['duser']) {
      $userid = checkUserid();
      $sql = "update pet_test_spa set utime = ". time() .", status = 1, duser = $userid where id = $data->id";
    }
    else $sql = "update pet_test_spa set utime = ". time() .", status = 2 where id = $data->id";
  }
  $db->query($sql);
  
  $result['status'] = 2;
  $result['time'] = time();
  $result['list'] = getList();

  return $result;
}

function returned() {
  global $data, $db, $result;

  if (!empty($data->uid)) $sql = "update pet_test_spa set utime = ". time() .", duser = $data->uid, status = 3 where id = $data->id";
  else {
    $sql = "select * from pet_test_spa where id = $data->id";
    $s = $db->fetch($sql);
    if (!$s['duser']) {
      $userid = checkUserid();
      $sql = "update pet_test_spa set utime = ". time() .", status = 1, duser = $userid where id = $data->id";
    }
    else $sql = "update pet_test_spa set utime = ". time() .", status = 3 where id = $data->id";
  }
  $db->query($sql);
  
  $result['status'] = 3;
  $result['time'] = time();
  $result['list'] = getList();

  return $result;
}

function report() {
  global $data, $db, $result;

  $sql = "update pet_test_spa set duser = $data->uid, dimage = '". implode(', ', $data->image) ."' where id = $data->id";
  $db->query($sql);
  
  $result['status'] = 1;
  return $result;
}

function coverData($data) {
  global $db;

  $list = array();
  foreach ($data as $key => $row) {
    $sql = "select b.value from pet_test_spa_row a inner join pet_test_config b on a.spaid = $row[id] and a.typeid = b.id";
    $service = $db->arr($sql, 'value');
  
    $sql = "select first_name as name from pet_users where userid = $row[duser]";
    $d = $db->fetch($sql);
  
    $image = explode(', ', $row['image']);
    $dimage = explode(', ', $row['dimage']);
    $list []= array(
      'id' => $row['id'],
      'name' => $row['name'],
      'phone' => $row['phone'],
      'rate' => intval($row['rate']),
      'duser' => (empty($d['name']) ? '' : $d['name']),
      'note' => $row['note'],
      'status' => $row['status'],
      'image' => (count($image) && !empty($image[0]) ? $image : array()),
      'dimage' => (count($dimage) && !empty($dimage[0]) ? $dimage : array()),
      'hour' => date('H:i', $row['time']),
      'date' => date('d/m/Y', $row['time']),
      'service' => (count($service) ? implode(', ', $service) : '-')
    );
  }
  return $list;
}

function search() {
  global $data, $db, $result;

  $sql = "select a.*, b.name, b.phone, c.first_name as user from pet_test_spa a inner join pet_test_customer b on a.customerid = b.id inner join pet_users c on a.doctorid = c.userid where (b.name like '%$data->keyword%' or b.phone like '%$data->keyword%') order by time desc limit 30";
  
  $result['status'] = 1;
  $result['list'] = coverData($db->all($sql));

  return $result;
}

function rate() {
  global $data, $db, $result;

  $sql = "update pet_test_spa set rate = $data->rate where id = $data->id";
  $db->query($sql);

  $sql = "select a.*, b.name, b.phone, c.first_name as user from pet_test_spa a inner join pet_test_customer b on a.customerid = b.id inner join pet_users c on a.doctorid = c.userid where (b.name like '%$data->keyword%' or b.phone like '%$data->keyword%') order by time desc limit 30";
  
  $result['status'] = 1;
  $result['list'] = coverData($db->all($sql));

  return $result;
}

function statrate() {
  global $data, $db, $result;

  $sql = "update pet_test_spa set rate = $data->rate where id = $data->id";
  $db->query($sql);

  $data->from = totime($data->from);
  $data->end = totime($data->end);
  $sql = "select a.*, b.name, b.phone, c.first_name as user from pet_test_spa a inner join pet_test_customer b on a.customerid = b.id inner join pet_users c on a.doctorid = c.userid where (a.time between $data->from and $data->end) order by time desc";
  
  $result['status'] = 1;
  $result['list'] = coverData($db->all($sql));

  return $result;
}

function statistic() {
  global $data, $db, $result;

  $data->from = totime($data->from);
  $data->end = totime($data->end);
  $sql = "select a.*, b.name, b.phone, c.first_name as user from pet_test_spa a inner join pet_test_customer b on a.customerid = b.id inner join pet_users c on a.doctorid = c.userid where (a.time between $data->from and $data->end) order by time desc";
  
  $result['status'] = 1;
  $result['list'] = coverData($db->all($sql));

  return $result;
}

function insert() {
  global $data, $db, $result;

  $sql = "select * from pet_test_customer where phone = '$data->phone'";
  if (!empty($customer = $db->fetch($sql))) {
    $sql = "update pet_test_customer set name = '$data->name' where id = $customer[id]";
    $db->query($sql);
  }
  else {
    $sql = "insert into pet_test_customer (name, phone, address) values ('$data->name', '$data->phone', '')";
    $customer['id'] = $db->insertid($sql);
  }
  
  $customer2 = array('id' => 0);
  if (!empty($data->phone2) && !empty($data->name2)) {
    $sql = "select * from pet_test_customer where phone = '$data->phone2'";
    if (!empty($customer2 = $db->fetch($sql))) {
      $sql = "update pet_test_customer set name = '$data->name2' where id = $customer2[id]";
      $db->query($sql);
    }
    else {
      $sql = "insert into pet_test_customer (name, phone, address) values ('$data->name2', '$data->phone2', '')";
      $customer2['id'] = $db->insertid($sql);
    }
  }

  $userid = checkUserid();
  
  $sql = "insert into pet_test_spa (customerid, customerid2, doctorid, note, time, utime, weight, image) values($customer[id], $customer2[id], $userid, '$data->note', '" . time() . "', '" . time() . "', $data->weight, '". implode(', ', $data->image)."')";
  $id = $db->insertid($sql);
  
  foreach ($data->option as $value) {
    $sql = "insert into pet_test_spa_row (spaid, typeid) values($id, $value)";
    $db->query($sql);
  }
  
  $result['time'] = time();
  $result['list'] = getList();
  $result['status'] = 1;
  
  return $result;
}

function update() {
  global $data, $db, $result;

  $sql = "select * from pet_test_customer where phone = '$data->phone'";
  if (!empty($customer = $db->fetch($sql))) {
    $sql = "update pet_test_customer set name = '$data->name' where id = $customer[id]";
    $db->query($sql);
  }
  else {
    $sql = "insert into pet_test_customer (name, phone, address) values ('$data->name', '$data->phone', '')";
    $customer['id'] = $db->insertid($sql);
  }
  
  $customer2 = array('id' => 0);
  if (!empty($data->phone2) && !empty($data->name2)) {
    $sql = "select * from pet_test_customer where phone = '$data->phone2'";
    if (!empty($customer2 = $db->fetch($sql))) {
      $sql = "update pet_test_customer set name = '$data->name2' where id = $customer2[id]";
      $db->query($sql);
    }
    else {
      $sql = "insert into pet_test_customer (name, phone, address) values ('$data->name2', '$data->phone2', '')";
      $customer2['id'] = $db->insertid($sql);
    }
  }
  
  $userid = checkUserid();

  $sql = "update pet_test_spa set customerid = $customer[id], customerid2 = $customer2[id], doctorid = $userid, note = '$data->note', image = '". implode(', ', $data->image)."', weight = $data->weight, utime = ". time() .", luser = $userid, ltime = ". time() .", status = 0, duser = 0 where id = $data->id";
  $db->query($sql);  
  
  $sql = "delete from pet_test_spa_row where spaid = $data->id";
  $db->query($sql);
  
  foreach ($data->option as $value) {
    $sql = "insert into pet_test_spa_row (spaid, typeid) values($data->id, $value)";
    $db->query($sql);
  }
  
  $result['time'] = time();
  $result['list'] = getList();
  $result['status'] = 1;

  return $result;
}

function getList() {
  global $data, $db;

  $time = strtotime(date('Y/m/d', $data->time / 1000));
  $end = $time + 60 * 60 * 24 - 1;
  $sql = "select a.*, b.name, b.phone, c.first_name as user from pet_test_spa a inner join pet_test_customer b on a.customerid = b.id inner join pet_users c on a.doctorid = c.userid where (time between $time and $end) and status < 3 order by utime desc";
  $spa = $db->all($sql);
  
  $sql = "select a.*, b.name, b.phone, c.first_name as user from pet_test_spa a inner join pet_test_customer b on a.customerid = b.id inner join pet_users c on a.doctorid = c.userid where (time between $time and $end) and status = 3 order by utime desc";
  $spa = array_merge($spa, $db->all($sql));

  $sql = "select * from pet_test_config where module = 'spa'";
  $option_list = $db->obj($sql, 'name');

  $list = array();
  foreach ($spa as $row) {
    $sql = "select b.value from pet_test_spa_row a inner join pet_test_config b on a.spaid = $row[id] and a.typeid = b.id";
    $service = $db->arr($sql, 'value');

    $sql = "select b.id from pet_test_spa_row a inner join pet_test_config b on a.spaid = $row[id] and a.typeid = b.id";
    $option = $db->arr($sql, 'id');

    $sql = "select name, phone from pet_test_customer where id = $row[customerid2]";
    $c = $db->fetch($sql);

    $sql = "select first_name as name from pet_users where userid = $row[luser]";
    $u = $db->fetch($sql);

    $sql = "select first_name as name from pet_users where userid = $row[duser]";
    $d = $db->fetch($sql);

    $image = explode(', ', $row['image']);
    $dimage = explode(', ', $row['dimage']);
    $list []= array(
      'id' => $row['id'],
      'name' => $row['name'],
      'phone' => $row['phone'],
      'name2' => (empty($c['name']) ? '' : $c['name']),
      'phone2' => (empty($c['phone']) ? '' : $c['phone']),
      'user' => $row['user'],
      'note' => $row['note'],
      'ltime' => (empty($u['name']) ? '' : date('d/m/Y H:i', $row['ltime'])),
      'luser' => (empty($u['name']) ? '' : $u['name']),
      'duser' => (empty($d['name']) ? '' : $d['name']),
      'duserid' => $row['duser'],
      'status' => $row['status'],
      'weight' => $row['weight'],
      'image' => (count($image) && !empty($image[0]) ? $image : array()),
      'dimage' => (count($dimage) && !empty($dimage[0]) ? $dimage : array()),
      'ftime' => date('d/m/Y', $row['time']),
      'time' => date('H:i', $row['time']),
      'option' => $option,
      'service' => (count($service) ? implode(', ', $service) : '-')
    );
  }

  return $list;
}

function gettypeobj() {
  global $db;
  $list = array();
  $sql = "select id, value from pet_test_config where module = 'spa'";
  $query = $db->query($sql);

  while ($row = $query->fetch_assoc()) {
    $row ['check'] = 0;
    $list[]= $row;
  }
  return $list;
}
