<?php
function add() {
  global $data, $db, $result;

  $sql = "insert into pet_test_xray_his (petid, his, time) values($data->petid, '$data->his', ". time() .")";
  query($sql);
  
  $sql = "select * from pet_test_xray_his where petid = $data->petid";
  $his = obj($sql, 'id', 'his');
  
  $result['status'] = 1;
  $result['his'] = implode(', ', $his);
  
  return $result;
}

function update() {
  global $data, $db, $result;

  $sql = "update pet_test_xray_row set doctorid = $userid, eye = '$data->eye', temperate = '$data->temperate', other = '$data->other', treat = '$data->treat', status = '$data->status' where id = $data->id";
  query($sql);
  
  $sql = "select a.*, b.name as pet, c.name as customer, c.phone, d.first_name as doctor from pet_test_xray a inner join pet_test_pet b on a.petid = b.id inner join pet_test_customer c on b.customerid = c.id inner join pet_users d on a.doctorid = d.userid order by id desc limit 20";
  $list = all($sql);
  
  foreach ($list as $key => $value) {
    $sql = "select a.*, b.first_name as doctor from pet_test_xray_row a inner join pet_users b on a.doctorid = b.userid where a.xrayid = $value[id] order by time asc";
    $row = all($sql);
    foreach ($row as $index => $detail) {
      $row[$index]['time'] = date('d/m/Y', $detail['time']);
    }
  
    $sql = "select * from pet_test_xray_his where petid = $value[petid]";
    $his = obj($sql, 'id', 'his');
  
    $list[$key]['status'] = $row[count($row) - 1]['status'];
    $list[$key]['detail'] = $row;
    $list[$key]['time'] = date('d/m/Y', $value['time']);
    $list[$key]['his'] = implode(', ', $his);
  }
  
  $result['status'] = 1;
  $result['list'] = $list;  
  
  return $result;
}

function statistic() {
  global $data, $db, $result;

  $sql = "select a.*, b.name as pet, c.name as customer, c.phone, d.first_name as doctor from pet_test_xray a inner join pet_test_pet b on a.petid = b.id inner join pet_test_customer c on b.customerid = c.id inner join pet_users d on a.doctorid = d.userid where (a.time between ". isototime($data->filter->from) ." and ". isototime($data->filter->end) .") or (a.time < ". isototime($data->filter->from) ." and a.insult = 0) order by id desc";
  $list = all($sql);
  $data = array();
  
  foreach ($list as $key => $value) {
    if (empty($data[$value['doctorid']])) $data[$value['doctorid']] = array(0 => 0, 1 => 0, 2 => 0, 3 => 0, 'name' => $value['doctor']);
    $data[$value['doctorid']] [$value['insult']] ++;
    $data[$value['doctorid']] [3] ++;
  }
  
  $stat = array();
  
  foreach ($data as $key => $value) {
    if ($value[2] > $value[1]) $data[$key]['balance'] = 2;
    else if ($value[1] > $value[2]) $data[$key]['balance'] = 1;
    else $data[$key]['balance'] = 0;
    $stat []= $data[$key];
  }
  
  $result['status'] = 1;
  $result['data'] = $stat;
  
  return $result;
}

function returned() {
  global $data, $db, $result;

  $sql = "update pet_test_xray set insult = 1 where id = $data->id";
  query($sql);
  
  $result['status'] = 1;
  $result['insult'] = 1;
  
  return $result;
}

function remove() {
  global $data, $db, $result;

  $sql = "delete from pet_test_xray where id = $data->id";
  query($sql);
  
  $sql = "delete from pet_test_xray_row where xrayid = $data->id";
  query($sql);
  
  $sql = "select a.*, b.name as pet, c.name as customer, c.phone, d.first_name as doctor from pet_test_xray a inner join pet_test_pet b on a.petid = b.id inner join pet_test_customer c on b.customerid = c.id inner join pet_users d on a.doctorid = d.userid where (a.time between ". isototime($data->filter->from) ." and ". isototime($data->filter->end) .") or (a.time < ". isototime($data->filter->from) ." and a.insult = 0) order by id desc";
  $list = all($sql);
  
  foreach ($list as $key => $value) {
    $sql = "select a.*, b.first_name as doctor, a.time from pet_test_xray_row a inner join pet_users b on a.doctorid = b.userid where a.xrayid = $value[id] order by time asc";
    $row = all($sql);
    foreach ($row as $index => $detail) {
      $row[$index]['time'] = date('d/m/Y', $detail['time']);
    }
  
    $sql = "select * from pet_test_xray_his where petid = $value[petid]";
    $his = obj($sql, 'id', 'his');
  
    $list[$key]['status'] = $row[count($row) - 1]['status'];
    $list[$key]['detail'] = $row;
    $list[$key]['time'] = date('d/m/Y', $value['time']);
    $list[$key]['his'] = implode(', ', $his);
  }
  
  $result['status'] = 1;
  $result['messenger'] = 'Đã xóa hồ sơ';
  $result['list'] = $list;
  
  return $result;
}

function pet() {
  global $data, $db, $result;

  $sql = "select * from pet_test_customer where phone = '$data->phone'";
  if (empty($c = fetch($sql))) {
    $sql = "insert into pet_test_customer (name, phone, addess) values('$data->customer', '$data->phone', '')";
    $c['id'] = insertid($sql);
  }
  
  $sql = "insert into pet_test_pet (name, customerid) values('$data->name', $c[id])";
  $p['id'] = insertid($sql);
  
  $sql = "select id, name from pet_test_pet where customerid = $c[id]";
  $list = all($sql);
  
  $result['status'] = 1;
  $result['petid'] = $p['id'];
  $result['petlist'] = $list;
  
  return $result;
}

function insert() {
  global $data, $db, $result;

  $sql = "select * from pet_test_customer where phone = '$data->phone'";

  if (empty($c = fetch($sql))) {
    $sql = "insert into pet_test_customer (name, phone, addess) values('$data->name', '$data->phone', '')";
    $c['id'] = insertid($sql);
  }
  
  $sql = "select * from pet_test_pet where id = $data->pet";
  if (empty($p = fetch($sql))) {
    $sql = "insert into pet_test_pet (name, customerid) values('Chưa đặt tên', $c[id])";
    $p['id'] = insertid($sql);
  }
  
  $sql = "insert into pet_test_xray(petid, doctorid, insult, time) values($p[id], $userid, 0, ". time() .")";
  $id = insertid($sql);
  
  $sql = "insert into pet_test_xray_row (xrayid, doctorid, eye, temperate, other, treat, image, status, time) values($id, $userid, '$data->eye', '$data->temperate', '$data->other', '$data->treat', '', '$data->status', ". time() .")";
  query($sql);
  
  $sql = "select a.*, b.name as pet, c.name as customer, c.phone, d.first_name as doctor from pet_test_xray a inner join pet_test_pet b on a.petid = b.id inner join pet_test_customer c on b.customerid = c.id inner join pet_users d on a.doctorid = d.userid where (a.time between ". isototime($data->filter->from) ." and ". isototime($data->filter->end) .") or (a.time < ". isototime($data->filter->from) ." and a.insult = 0) order by id desc";
  $list = all($sql);
  
  foreach ($list as $key => $value) {
    $sql = "select a.*, b.first_name as doctor, a.time from pet_test_xray_row a inner join pet_users b on a.doctorid = b.userid where a.xrayid = $value[id] order by time asc";
    $row = all($sql);
    foreach ($row as $index => $detail) {
      $row[$index]['time'] = date('d/m/Y', $detail['time']);
    }
  
    $sql = "select * from pet_test_xray_his where petid = $value[petid]";
    $his = obj($sql, 'id', 'his');
  
    $list[$key]['status'] = $row[count($row) - 1]['status'];
    $list[$key]['detail'] = $row;
    $list[$key]['time'] = date('d/m/Y', $value['time']);
    $list[$key]['his'] = implode(', ', $his);
  }
  
  $result['status'] = 1;
  $result['list'] = $list;
  
  return $result;
}

function filter() {
  global $data, $db, $result;

  $sql = "select a.*, b.name as pet, c.name as customer, c.phone, d.first_name as doctor from pet_test_xray a inner join pet_test_pet b on a.petid = b.id inner join pet_test_customer c on b.customerid = c.id inner join pet_users d on a.doctorid = d.userid where (a.time between ". isototime($data->filter->from) ." and ". isototime($data->filter->end) .") or (a.time < ". isototime($data->filter->from) ." and a.insult = 0) order by id desc";
  $list = all($sql);
  
  foreach ($list as $key => $value) {
    $sql = "select a.*, b.first_name as doctor, a.time from pet_test_xray_row a inner join pet_users b on a.doctorid = b.userid where a.xrayid = $value[id] order by time asc";
    $row = all($sql);
    foreach ($row as $index => $detail) {
      $row[$index]['time'] = date('d/m/Y', $detail['time']);
    }
  
    $sql = "select * from pet_test_xray_his where petid = $value[petid]";
    $his = obj($sql, 'id', 'his');
  
    $list[$key]['status'] = $row[count($row) - 1]['status'];
    $list[$key]['detail'] = $row;
    $list[$key]['time'] = date('d/m/Y', $value['time']);
    $list[$key]['his'] = implode(', ', $his);
  }
  
  $result['status'] = 1;
  $result['list'] = $list;

  return $result;
}

function detail() {
  global $data, $db, $result;

  $time = explode('T', $data->time);
  $time = explode('-', $time[0]);
  $time = totime("$time[2]/$time[1]/$time[0]");
  
  $sql = "insert into pet_test_xray_row (xrayid, doctorid, eye, temperate, other, treat, image, status, time) values($data->id, $userid, '$data->eye', '$data->temperate', '$data->other', '$data->treat', '', '$data->status', $time)";
  $id = insertid($sql);
  
  $sql = "select a.*, b.first_name as doctor, a.time from pet_test_xray_row a inner join pet_users b on a.doctorid = b.userid where a.id = $id order by time asc";
  $row = fetch($sql);
  $row['time'] = date('d/m/Y', $row['time']);
  
  $result['status'] = 1;
  $result['data'] = $row;

  return $result;
}

function dead() {
  global $data, $db, $result;

  $sql = "update pet_test_xray set insult = 2 where id = $data->id";
  query($sql);

  $result['status'] = 1;
  $result['insult'] = 2;

  return $result;
}
