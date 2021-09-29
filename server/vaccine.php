<?php
function auto() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['list'] = getlist();
  $result['new'] = getlist(true);
  $result['disease'] = getDisease();

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

  $data->cometime = totime($data->cometime);
  $data->calltime = totime($data->calltime);
  $userid = checkUserid();
  $sql = "insert into pet_test_vaccine (customerid, diseaseid, cometime, calltime, note, status, called, recall, userid, time) values ($customer[id], $data->disease, $data->cometime, $data->calltime, '', 0, 0, $data->calltime, $userid, ". time() .")";
  $db->query($sql);
  $result['status'] = 1;
  $result['new'] = getlist(true);
  $result['old'] = getOlder($customer['id']);
  return $result;
}

function update() {
  global $data, $db, $result;

  $data->cometime = totime($data->cometime);
  $data->calltime = totime($data->calltime);
  $userid = checkUserid();
  $sql = "update pet_test_vaccine set diseaseid = $data->disease, cometime = $data->cometime, calltime = $data->calltime where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['list'] = getlist();
  $result['new'] = getlist(true);
  return $result;
}

function done() {
  global $data, $db, $result;

  $sql = "update pet_test_vaccine set status = 2, recall = ". time() ." where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['list'] = getlist();
  $result['old'] = getOlder($data->customerid);
  return $result;
}

function called() {
  global $data, $db, $result;

  $sql = "update pet_test_vaccine set status = 1, note = '". $data->note ."', called = ". time() .", recall = ". (time() + 60 * 60 * 24 * 7) ." where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['list'] = getlist();
  return $result;
}

function uncalled() {
  global $data, $db, $result;

  $sql = "update pet_test_vaccine set status = 1, note = '". $data->note ."', called = ". time() .", recall = ". (time() + 60 * 60 * 24) ." where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['list'] = getlist();
  return $result;
}

function dead() {
  global $data, $db, $result;

  $sql = "update pet_test_vaccine set status = 2, recall = ". time() .", note = '$data->note' where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['list'] = getlist();
  return $result;
}

function search() {
  $result['status'] = 1;
  $result['list'] = getlist();
  return $result;
}

function getlist($today = false) {
  global $db, $data;

  $disease = diseaseList();
  $start = strtotime(date('Y/m/d'));
  if ($today) {
    $sql = 'select a.*, b.name, b.phone, b.address from pet_test_vaccine a inner join pet_test_customer b on a.customerid = b.id where (a.time between '. $start . ' and '. time() . ') and a.status < 2 order by a.id desc limit 50';
  }
  else if (empty($data->filter->keyword)) {
    $end = $start + 60 * 60 * 24 * 7 - 1; 
    $sql = 'select a.*, b.name, b.phone, b.address from pet_test_vaccine a inner join pet_test_customer b on a.customerid = b.id where a.recall < '. $end . ' and a.status < 2 order by a.calltime asc, a.recall desc limit 50';
  }
  else {
    $key = $data->filter->keyword;
    $sql = "select a.*, b.name, b.phone, b.address from pet_test_vaccine a inner join pet_test_customer b on a.customerid = b.id where (b.name like '%$key%' or b.phone like '%$key%') order by a.calltime asc, a.recall desc limit 50";
  }

  $query = $db->query($sql);
  $list = array();

  // luật tính status
  // nếu chưa gọi, chưa cách quá 7 ngày, status = 0
  // nếu đã gọi, chưa cách quá 7 ngày status = 1
  // nếu đã gọi, cách quá 7 ngày status = 2
  // nếu chưa gọi, cách quá 7 ngày, status = 3

  $limit = $start - 60 * 60 * 24 * 7;
  while ($row = $query->fetch_assoc()) {
    $status = $row['status'];
    if ($status) {
      if ($row['calltime'] < $limit) $status = 2;
      else $status = 1;
    }
    else {
      if ($row['calltime'] < $limit) $status = 3;
      else $status = 0;
    }

    $list []= array(
      'id' => $row['id'],
      'note' => $row['note'],
      'name' => $row['name'],
      'phone' => $row['phone'],
      'address' => $row['address'],
      'status' => $status,
      'vaccine' => $disease[$row['diseaseid']],
      'called' => ($row['called'] ? date('d/m/Y', $row['called']) : '-'),
      'cometime' => date('d/m/Y', $row['cometime']),
      'calltime' => date('d/m/Y', $row['calltime']),
    );
  }

  return $list;
}

function getOlder($customerid) {
  global $db;

  $sql = "select * from pet_test_vaccine where status < 2 and customerid = $customerid order by id asc";
  $list = $db->all($sql);
  $query = $db->query($sql);
  $disease = diseaseList();
  foreach ($list as $index => $row) {
    $list[$index]['disease'] = $disease[$row['diseaseid']];
    $list[$index]['calltime'] = date('d/m/Y', $row['calltime']);
    $list[$index]['called'] = ($row['called'] ? date('d/m/Y', $row['called']) : '-');
  }

  return $list;
}

function getCustomer($petid) {
  global $db;

  $sql = "select * from pet_test_pet where id = $petid";
  $pet = $db->fetch($sql);

  $sql = "select * from pet_test_customer where id = $pet[customerid]";
  return $db->fetch($sql);
}

function diseaseList() {
  global $db;
  $sql = 'select * from `pet_test_disease`';
  return $db->obj($sql, 'id', 'name');
}

function getDisease() {
  global $db;
  $sql = 'select * from `pet_test_disease`';
  return $db->all($sql);
}
