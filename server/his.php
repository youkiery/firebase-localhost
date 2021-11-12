<?php
function add() {
  global $data, $db, $result;

  $sql = "insert into pet_test_xray_his (petid, his, time) values($data->petid, '$data->his', ". time() .")";
  $db->query($sql);
  
  $sql = "select * from pet_test_xray_his where petid = $data->petid";
  $his = $db->obj($sql, 'id', 'his');
  
  $result['status'] = 1;
  $result['his'] = implode(', ', $his);
  
  return $result;
}

function update() {
  global $data, $db, $result;

  $sql = "update pet_test_xray_row set eye = '$data->eye', temperate = '$data->temperate', other = '$data->other', treat = '$data->treat', status = '$data->status', image = '". implode(', ', $data->image) ."' where id = $data->detailid";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['data'] = getlist($data->id);
  
  return $result;
}

function statistic() {
  global $data, $db, $result;

  $data->from = isodatetotime($data->from);
  $data->end = isodatetotime($data->end) + 60 * 60 * 24 - 1;

  $userid = checkuserid();
  $sql = "select * from pet_test_user_per where userid = $userid and module = 'his' and type = 2";
  $xtra = "";
  if (empty($p = $db->fetch($sql))) $xtra = "a.doctorid = $userid and";

  $sql = "select a.*, b.name as pet, c.name as customer, c.phone, d.name as doctor from pet_test_xray a inner join pet_test_pet b on a.petid = b.id inner join pet_test_customer c on b.customerid = c.id inner join pet_users d on a.doctorid = d.userid where $xtra (a.time between $data->from and $data->end) order by id desc";
  $list = $db->all($sql);
  $data = array();
  
  foreach ($list as $key => $value) {
    if (empty($data[$value['doctorid']])) $data[$value['doctorid']] = array(0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0, 'name' => $value['doctor']);
    $data[$value['doctorid']] [$value['insult']] ++;
    $data[$value['doctorid']] [3] ++;
    $data[$value['doctorid']] [4] += $value['rate'];
  }
  
  $stat = array();
  
  foreach ($data as $key => $value) {
    if ($value[2] > $value[1]) $data[$key]['balance'] = 2;
    else if ($value[1] > $value[2]) $data[$key]['balance'] = 1;
    else $data[$key]['balance'] = 0;
    $stat []= $data[$key];
  }
  
  usort($data, "ratest");

  $result['status'] = 1;
  $result['data'] = $stat;
  
  return $result;
}

function ratest($a, $b) {
  return $a[4] > $b[4];
}

function returned() {
  global $data, $db, $result;

  $sql = "update pet_test_xray set insult = 1 where id = $data->id";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['insult'] = 1;
  
  return $result;
}

function remove() {
  global $data, $db, $result;

  $sql = "delete from pet_test_xray where id = $data->id";
  $db->query($sql);
  
  $sql = "delete from pet_test_xray_row where xrayid = $data->id";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['messenger'] = 'Đã xóa hồ sơ';
  $result['list'] = getlist();
  
  return $result;
}

function pet() {
  global $data, $db, $result;

  $sql = "select * from pet_test_customer where phone = '$data->phone'";

  if (empty($c = $db->fetch($sql))) {
    $sql = "insert into pet_test_customer (name, phone, addess) values('$data->name', '$data->phone', '')";
    $c['id'] = $db->insertid($sql);
  }
  
  $sql = "select * from pet_test_pet where id = $data->pet";
  if (empty($p = $db->fetch($sql))) {
    $sql = "insert into pet_test_pet (name, customerid) values('Chưa đặt tên', $c[id])";
    $p['id'] = $db->insertid($sql);
  }
  
  $sql = "select id, name from pet_test_pet where customerid = $c[id]";
  $list = $db->all($sql);
  
  $result['status'] = 1;
  $result['petid'] = $p['id'];
  $result['petlist'] = $list;
  
  return $result;
}

function insert() {
  global $data, $db, $result;

  $petid = checkpet();
  $userid = checkuserid();
  
  $sql = "insert into pet_test_xray(petid, doctorid, insult, time) values($petid, $userid, 0, ". time() .")";
  $id = $db->insertid($sql);
  
  $sql = "insert into pet_test_xray_row (xrayid, doctorid, eye, temperate, other, treat, image, status, time) values($id, $userid, '$data->eye', '$data->temperate', '$data->other', '$data->treat', '". implode(', ', $data->image) ."', '$data->status', ". time() .")";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['list'] = getlist();
  
  return $result;
}

function filter() {
  global $data, $db, $result;
  
  $result['status'] = 1;
  $result['list'] = getlist();

  return $result;
}

function detail() {
  global $data, $db, $result;

  $time = time();
  $userid = checkuserid();

  $sql = "insert into pet_test_xray_row (xrayid, doctorid, eye, temperate, other, treat, status, time, image) values($data->id, $userid, '$data->eye', '$data->temperate', '$data->other', '$data->treat', '$data->status', $time, '". implode(', ', $data->image) ."')";
  $id = $db->insertid($sql);
  
  $sql = "select a.*, b.name as doctor, a.time from pet_test_xray_row a inner join pet_users b on a.doctorid = b.userid where a.id = $id order by time asc";
  $row = $db->fetch($sql);
  $row['time'] = date('d/m/Y', $row['time']);
  
  $result['status'] = 1;
  $result['data'] = getlist($data->id);

  return $result;
}

function dead() {
  global $data, $db, $result;

  $sql = "update pet_test_xray set insult = 2 where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['insult'] = 2;

  return $result;
}

function getlist($id = 0) {
  global $db, $data;

  $data->from = isodatetotime($data->from);
  $data->end = isodatetotime($data->end) + 60 * 60 * 24 - 1;

  $sql = "select a.*, b.name as pet, c.name as customer, c.phone, d.name as doctor from pet_test_xray a inner join pet_test_pet b on a.petid = b.id inner join pet_test_customer c on b.customerid = c.id inner join pet_users d on a.doctorid = d.userid where (a.time between $data->from and $data->end) or (a.time < $data->from and a.insult = 0) ". ($id ? " and a.id = $id " : '') ." order by id desc";
  $list = $db->all($sql);
  
  foreach ($list as $key => $value) {
    $sql = "select a.*, b.name as doctor, a.time from pet_test_xray_row a inner join pet_users b on a.doctorid = b.userid where a.xrayid = $value[id] order by time asc";
    $row = $db->all($sql);
    foreach ($row as $index => $detail) {
      $row[$index]['time'] = date('d/m/Y', $detail['time']);
      $image = explode(', ', $detail['image']);
      if (count($image) == 1 && $image[0] == '') $row[$index]['image'] = array();
      else $row[$index]['image'] = $image;
    }
  
    $sql = "select * from pet_test_xray_his where petid = $value[petid]";
    $his = $db->obj($sql, 'id', 'his');
  
    $list[$key]['status'] = $row[count($row) - 1]['status'];
    $list[$key]['rate'] = intval($value['rate']);
    $list[$key]['detail'] = $row;
    $list[$key]['time'] = date('d/m/Y', $value['time']);
    $list[$key]['his'] = implode(', ', $his);
  }
  return $list;
}

function statrate() {
  global $data, $db, $result;

  $sql = "update pet_test_xray set rate = $data->rate where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = getlist();

  return $result;
}

function checkpet() {
  global $data, $db;
  $sql = "select * from pet_test_customer where phone = '$data->phone'";

  if (empty($c = $db->fetch($sql))) {
    $sql = "insert into pet_test_customer (name, phone, addess) values('$data->name', '$data->phone', '')";
    $c['id'] = $db->insertid($sql);
  }
  
  $sql = "select * from pet_test_pet where name = '$data->pet' and customerid = '$c[id]'";
  if (empty($p = $db->fetch($sql))) {
    $sql = "insert into pet_test_pet (name, customerid) values('$data->pet', $c[id])";
    $p['id'] = $db->insertid($sql);
  }
  return $p['id'];
}
