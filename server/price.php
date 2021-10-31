<?php
function init() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['list'] = getlist();
  return $result;
}

function insert() {
  global $data, $db, $result;
 
  $sql = "insert into pet_test_price (name, unit) values('$data->name', '$data->unit')";
  $itemid = $db->insertid($sql);

  foreach ($data->detail as $detail) {
    $detail->price = str_replace(',', '', $detail->price);
    $sql = "insert into pet_test_price_detail (itemid, name, price) values($itemid, '$detail->name', '$detail->price')";
    $db->query($sql);
  }
 
  $result['status'] = 1;
  $result['list'] = getlist();
  return $result; 
}

function remove() {
  global $data, $db, $result;

  $sql = "delete from pet_test_price where id = $data->id";
  $db->query($sql);
  $sql = "delete from pet_test_price_detail where itemid = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = getlist();
  return $result; 
}

function update() {
  global $data, $db, $result;

  $sql = "delete from pet_test_price_detail where itemid = $data->id";
  $db->query($sql);

  $sql = "update pet_test_price set name = '$data->name', unit = '$data->unit' where id = $data->id";
  $db->query($sql);

  foreach ($data->detail as $detail) {
    $detail->price = str_replace(',', '', $detail->price);
    $sql = "insert into pet_test_price_detail (itemid, name, price) values($data->id, '$detail->name', '$detail->price')";
    $db->query($sql);
  }
  
  $result['status'] = 1;
  $result['list'] = getlist();
  return $result; 
}

function getlist() {
  global $db, $data;

  $xtra = "";
  if (isset($data->{'keyword'})) $xtra = "where name like '%$data->keyword%'";
  $sql = "select * from pet_test_price $xtra order by id desc";
  $pl = $db->all($sql); 

  foreach ($pl as $i => $p) {
    $sql = "select * from pet_test_price_detail where itemid = '$p[id]' order by id asc";
    $pl[$i]['detail'] = $db->all($sql);
  }
  return $pl;
}
