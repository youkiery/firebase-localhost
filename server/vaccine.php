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

  $sql = "select * from pet_test_pet where customerid = $customer[id]";
  if (empty($pet = $db->fetch($sql))) {
    $sql = "insert into pet_test_pet (name) values ('Chưa đặt tên')";
    $pet['id'] = $db->insertid($sql);
  }

  $data->cometime = totime($data->cometime);
  $data->calltime = totime($data->calltime);
  $userid = checkUserid();
  $sql = "insert into pet_test_vaccine (petid, diseaseid, cometime, calltime, note, status, recall, doctorid, ctime) values ($pet[id], $data->disease, $data->cometime, $data->calltime, '', 0, 0, $userid, ". time() .")";
  $db->query($sql);
  $result['status'] = 1;
  $result['new'] = getlist(true);
  $result['old'] = getOlder($customer['id']);
  return $result;
}

function update() {
  global $data, $db, $result;

  $sql = "select * from pet_test_customer where phone = '$data->phone'";
  $customer = $db->fetch($sql);
  $sql = "select * from pet_test_pet where customerid = $customer[id]";
  $pet = $db->fetch($sql);

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

function remove() {
  global $data, $db, $result;

  $sql = "delete from pet_test_vaccine where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
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

function note() {
  global $data, $db, $result;

  $sql = "update pet_test_vaccine set note = '$data->note' where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  return $result;
}

function called() {
  global $data, $db, $result;

  $sql = "update pet_test_vaccine set status = 1, recall = ". time() ." where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['list'] = getlist();
  return $result;
}

function dead() {
  global $data, $db, $result;

  $sql = "delete from pet_test_vaccine where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['list'] = getlist();
  return $result;
}

function getlist($today = false) {
  global $db;

  $disease = diseaseList();
  if ($today) {
    $start = strtotime(date('Y/m/d'));
    $end = time();
    $sql = 'select * from pet_test_vaccine where (ctime between '. $start . ' and '. $end . ') and status < 2 order by id desc limit 50';
  }
  else {
    $end = time() + 60 * 60 * 24 * 7; 
    $sql = 'select * from pet_test_vaccine where (recall < '. $end . ') and status < 2 order by recall desc limit 50';
  }

  $query = $db->query($sql);
  $list = array();

  while ($row = $query->fetch_assoc()) {
    $customer = getCustomer($row['petid']);
    if (!empty($customer['phone'])) {
      $list []= array(
        'id' => $row['id'],
        'note' => $row['note'],
        'name' => $customer['name'],
        'phone' => $customer['phone'],
        'vaccine' => $disease[$row['diseaseid']],
        'recall' => ($row['recall'] ? date('d/m/Y', $row['recall']) : 0),
        'cometime' => date('d/m/Y', $row['cometime']),
        'calltime' => date('d/m/Y', $row['calltime']),
      );
    }
  }

  return $list;
}

function getOlder($customerid) {
  global $db;

  $sql = "select a.id, a.diseaseid, a.calltime, a.recall, b.customerid from pet_test_vaccine a inner join pet_test_pet b on a.petid = b.id where a.status < 2 and b.customerid = $customerid order by id asc";
  $list = $db->all($sql);
  $query = $db->query($sql);
  $disease = diseaseList();
  foreach ($list as $index => $row) {
    $list[$index]['disease'] = $disease[$row['diseaseid']];
    $list[$index]['calltime'] = date('d/m/Y', $row['calltime']);
    $list[$index]['recall'] = ($row['recall'] ? date('d/m/Y', $row['recall']) : 0);
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
  return $db->object($sql, 'id', 'name');
}

function getDisease() {
  global $db;
  $sql = 'select * from `pet_test_disease`';
  return $db->all($sql);
}
