<?php

function expire() {
  global $data, $db, $result;

  $sql = "select * from pet_test_item where code = '$data->name'";
  if (empty($item = $db->fetch($sql))) {
    $sql = "insert into pet_test_item (name, code, shop, storage, catid, border, image) values('$data->name', '$data->code', 0, 0, 0, 10, '')";
    $item['id'] = $db->insertid($sql);
  }
  
  $data->expire = totime($data->expire);
  $sql = "insert into pet_test_item_expire (rid, number, expire, time) values($item[id], $data->number, $data->expire, ". time() .")";
  $db->query($sql);
  $result['status'] = 1;
  $result['messenger'] = 'Đã thêm hạn sử dụng';

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

function incat() {
  global $data, $db, $result;

  $sql = "select * from pet_test_item_cat where name = '$data->cat'";
  if (!empty($row = $db->fetch($sql))) $result['messenger'] = 'Danh mục đã tồn tại';
  else {
    $sql = "insert into pet_test_item_cat (name) values('$data->cat')";
    $result['status'] = 1;
    $result['cat'] = $db->insertid($sql);
    $result['catlist'] = getCatList();
  }

  return $result;
}

function init() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['purchase'] = getPurchase();
  $result['transfer'] = getTransfer();
  $result['expired'] = getExpire();
  $result['catlist'] = getCatList();
  $result['all'] = getSuggestList();
  $result['image'] = getImagePos();
  $result['list'] = getList();
  
  return $result;
}

function inpos() {
  global $data, $db, $result;

  $image = '';
  foreach ($data->image as $value) {
    if (strlen($value) > 50) $image = $value;
  }
  
  $sql = "insert into pet_test_item_pos (name, image) values('$data->pos', '$image')";
  
  $result['status'] = 1;
  $result['id'] = $db->insertid($sql);
  $result['image'] = $image;

  return $result;
}

function inpositem() {
  global $data, $db, $result;

  foreach ($data->list as $key => $value) {
    $sql = "select * from pet_test_item_pos_item where posid = $data->posid and itemid = $value->id";
    if (empty($db->fetch($sql))) {
      $sql = "insert into pet_test_item_pos_item (posid, itemid) values($data->posid, $value->id)";
      $db->query($sql);
    }
  }
  
  $sql = "select b.id, a.name from pet_test_item a inner join pet_test_item_pos_item b on a.id = b.itemid where b.posid = $data->posid";
  $result['list'] = $db->all($sql);
  $result['status'] = 1;
  
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

function insert() {
  global $data, $db, $result;

  $name_sql = "select * from pet_test_item where name = '$data->name'";
  $code_sql = "select * from pet_test_item where code = '$data->code'";
  if (!empty($db->fetch($name_sql))) $result['messenger'] = 'Tên mặt hàng đã tồn tại'; 
  else if (!empty($db->fetch($code_sql))) $result['messenger'] = 'Mã mặt hàng đã tồn tại'; 
  else {
    $sql = "insert into pet_test_item (name, code, shop, storage, catid, border, image) values('$data->name', '$data->code', 0, 0, $data->cat, 10, '". implode(', ', $data->image) ."')";
    $db->query($sql);
  
    $result['status'] = 1;
    $result['list'] = getList();
  }
    
  return $result;
}

function position_init() {
  global $data, $db, $result;

  $sql = "select * from pet_test_item_pos order by name asc";
  $list = $db->all($sql);
  
  foreach ($list as $key => $value) {
    $sql = "select * from pet_test_item a inner join pet_test_item_pos_item b on a.id = b.itemid where b.posid = $value[id]";
    $list[$key]['position'] = $db->all($sql);
  }
  
  $result['status'] = 1;
  $result['list'] = $list;
      
  return $result;
}

function position_remove() {
  global $data, $db, $result;

  $sql = "delete from pet_test_item_pos_item where posid = $data->id";
  $db->query($sql);

  $sql = "delete from pet_test_item_pos where id = $data->id";
  $db->query($sql);
  
  $result['status'] = 1;
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

function repos() {
  global $data, $db, $result;

  $sql = "delete from pet_test_item_pos_item where id = $data->itemid";
  $db->query($sql);
  
  $sql = "select b.id, a.name from pet_test_item a inner join pet_test_item_pos_item b on a.id = b.itemid where b.posid = $data->posid";
  $result['list'] = $db->all($sql);
  $result['status'] = 1;
  
  return $result;
}

function search() {
  global $data, $db, $result;

  $sql = "select name, code from pet_test_item where name like '%$data->key%'";
  
  $result['status'] = 1;
  $result['list'] = $db->all($sql);

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

function update() {
  global $data, $db, $result;

  $name_sql = "select * from pet_test_item where name = '$data->name' and id <> $data->id";
  $code_sql = "select * from pet_test_item where code = '$data->code' and id <> $data->id";
  if (!empty($db->fetch($name_sql))) $result['messenger'] = 'Tên mặt hàng đã tồn tại'; 
  else if (!empty($db->fetch($code_sql))) $result['messenger'] = 'Mã mặt hàng đã tồn tại'; 
  else {
    $sql = "update pet_test_item set name = '$data->name', code = '$data->code', border = '$data->border', image = '". implode(', ', $data->image) ."' where id = $data->id";
    $db->query($sql);
  
    $result['status'] = 1;
    $result['list'] = getList();
  }
  
  return $result;
}

function uppos() {
  global $data, $db, $result;
  $image = '';
  foreach ($data->image as $value) {
    if (strlen($value) > 50) $image = $value;
  }

  $sql = "update pet_test_item_pos set name = '$data->pos', image = '$image' where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;

  return $result;
}

function getList() {
  global $data, $db;
  
  $sql = "select * from pet_test_item where name like '%$data->keyword%' order by name asc";
  $list = $db->all($sql);

  foreach ($list as $key => $value) {
    $list[$key]['image'] = explode(', ', $value['image']);

    $sql = "select a.id, a.name from pet_test_item_pos a inner join pet_test_item_pos_item b on a.id = b.posid where b.itemid = $value[id]";
    $list[$key]['position'] = $db->all($sql);
  }

  return $list;
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

function getCatList() {
  global $data, $db;
  
  $sql = "select * from pet_test_item_cat order by name asc";
  return array_merge(array(array('id' => "0", 'name' => 'Chưa phân loại')), $db->all($sql));
}

function getSuggestList() {
  global $data, $db;
  
  $sql = "select id, name from pet_test_item order by name asc";
  $list = $db->all($sql);
  foreach ($list as $key => $value) {
    $list[$key]['alias'] = lower($value['name']);
  }
  return $list;
}

function getImagePos() {
  global $data, $db;
  
  $sql = "select id, image from pet_test_item_pos order by name asc";
  return $db->obj($sql, 'id', 'image');
}
