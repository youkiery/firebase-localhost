<?php

function init() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['purchase'] = getPurchase();
  $result['transfer'] = getTransfer();
  $result['expired'] = getExpire();
  $result['all'] = getSuggestList();
  $result['list'] = getList();
  return $result;
}

function filter() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['list'] = getList();
  return $result;
}

function purchase_init() {
  global $data, $db, $result;

  $sql = "select name, storage + shop as number from pet_test_item where storage + shop < border order by name asc";
  $list = $db->all($sql);

  $result['status'] = 1;
  $result['list'] = $list;
  return $result;
}

function transfer_init() {
  global $data, $db, $result;

  $sql = "select name, storage, shop from pet_test_item where shop < border and storage > 0 order by name asc";
  $list = $db->all($sql);

  $result['status'] = 1;
  $result['list'] = $list;
  return $result;
}

function expired_init() {
  global $data, $db, $result;

  $limit = time() * 60 * 60 * 24 * 60;
  $sql = "select a.id, b.name, a.expire from pet_test_item_expire a inner join pet_test_item b on a.rid = b.id where expire < $limit";
  $list = $db->all($sql);

  foreach ($list as $key => $value) {
    $list[$key]['expire'] = date('d/m/Y', $value['expire']);
  }

  $result['status'] = 1;
  $result['list'] = $list;
  return $result;
}

function expire_done() {
  global $data, $db, $result;

  $sql = "delete from pet_test_item_expire where id = $data->id";
  $db->query($sql);

  $limit = time() * 60 * 60 * 24 * 60;
  $sql = "select a.id, b.name, a.expire from pet_test_item_expire a inner join pet_test_item b on a.rid = b.id where expire < $limit";
  $list = $db->all($sql);

  foreach ($list as $key => $value) {
    $list[$key]['expire'] = date('d/m/Y', $value['expire']);
  }

  $result['status'] = 1;
  $result['expired'] = getExpire();
  $result['list'] = $list;
  return $result;
}

function insert() {
  global $data, $db, $result;

  $name_sql = "select * from pet_test_item where name = '$data->name'";
  $code_sql = "select * from pet_test_item where code = '$data->code'";
  if (!empty($db->fetch($name_sql))) $result['messenger'] = 'Tên mặt hàng đã tồn tại'; 
  else if (!empty($db->fetch($code_sql))) $result['messenger'] = 'Mã mặt hàng đã tồn tại'; 
  else {
    $sql = "insert into pet_test_item (name, code, shop, storage, position, border, image) values('$data->name', '$data->code', 0, 0, '', 10, '". str_replace('@@', '%2F', implode(', ', $data->image)) ."')";
    $db->query($sql);

    $result['status'] = 1;
    $result['list'] = getList();
  }
  return $result;
}

function update() {
  global $data, $db, $result;

  $name_sql = "select * from pet_test_item where name = '$data->name' and id <> $data->id";
  $code_sql = "select * from pet_test_item where code = '$data->code' and id <> $data->id";
  if (!empty($db->fetch($name_sql))) $result['messenger'] = 'Tên mặt hàng đã tồn tại'; 
  else if (!empty($db->fetch($code_sql))) $result['messenger'] = 'Mã mặt hàng đã tồn tại'; 
  else {
    $sql = "update pet_test_item set name = '$data->name', code = '$data->code', border = '$data->border', image = '". str_replace('@@', '%2F', implode(', ', $data->image)) ."' where id = $data->id";
    $db->query($sql);

    $result['status'] = 1;
    $result['list'] = getList();
  }
  return $result;
}

function remove() {
  global $data, $db, $result;

  $sql = "delete from pet_test_item where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = getList();
  return $result;
}

function expire() {
  global $data, $db, $result;

  $sql = "select * from pet_test_item where name = '$data->name'";
  if (empty($item = $db->fetch($sql))) {
    $sql = "insert into pet_test_item (name, code, shop, storage, position, border, image) values('$data->name', '". randomString() ."', 0, 0, '', 10, '')";
    $item['id'] = $db->insertid($sql);
  }

  $data->expire = totime($data->expire);
  $sql = "insert into pet_test_item_expire (rid, number, expire, time) values($item[id], $data->number, $data->expire, ". time() .")";
  $db->query($sql);
  $result['status'] = 1;
  $result['messenger'] = 'Đã thêm hạn sử dụng';
  return $result;
}

function search() {
  global $data, $db, $result;

  $sql = "select name, code from pet_test_item where name like '%$data->key%'";
  
  $result['status'] = 1;
  $result['list'] = $db->all($sql);
  return $result;
}

function inpos() {
  global $data, $db, $result;

  $sql = "update pet_test_item set position = '". implode(', ', $data->pos) ."' where id = $data->id";
  $db->query($sql);
  
  $result['status'] = 1;
  return $result;
}

function repos() {
  global $data, $db, $result;

  $sql = "update pet_test_item set position = '". implode(', ', $data->pos) ."' where id = $data->id";
  $db->query($sql);
  
  $result['status'] = 1;
  return $result;
}

function getPurchase() {
  global $data, $db;

  $sql = "select count(*) as number from pet_test_item where storage + shop < border";
  $number = $db->fetch($sql);
  return $number['number'];
}
function getTransfer() {
  global $data, $db;

  $sql = "select count(*) as number from pet_test_item where shop < border and storage > 0";
  $number = $db->fetch($sql);
  return $number['number'];
}
function getExpire() {
  global $data, $db;

  $limit = time() * 60 * 60 * 24 * 60;
  $sql = "select count(*) as number from pet_test_item_expire where expire < $limit";
  $number = $db->fetch($sql);
  return $number['number'];
}

function getList() {
  global $data, $db;
  
  $sql = "select * from pet_test_item where name like '%$data->keyword%' order by name asc";
  $list = $db->all($sql);

  foreach ($list as $key => $value) {
    $list[$key]['image'] = explode(', ', $value['image']);
  }

  return $list;
}

function getSuggestList() {
  global $data, $db;
  
  $sql = "select id, code, name, position from pet_test_item";
  $list = $db->all($sql);

  foreach ($list as $key => $value) {
    $position = array_filter(explode(', ', $value['position']));

    $list[$key]['position'] = $position;
  }

  return $list;
}
