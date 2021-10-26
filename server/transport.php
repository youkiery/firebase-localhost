<?php
function init() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['list'] = getlist();
  return $result;
}

function insert() {
  global $data, $db, $result;
 
  $sql = "insert into pet_test_transport (name, phone, address) values('$data->name', '$data->phone', '$data->address')";
  $itemid = $db->insertid($sql);

  foreach ($data->detail as $detail) {
    $sql = "insert into pet_test_config (module, name, value) values('transport', '$itemid', '$detail->name')";
    $db->query($sql);
  }
 
  $result['status'] = 1;
  $result['list'] = getlist();
  return $result; 
}

function remove() {
  global $data, $db, $result;

  $sql = "delete from pet_test_transport where id = $data->id";
  $db->query($sql);
  $sql = "delete from pet_test_config where module = 'transport' and name = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = getlist();
  return $result; 
}

function update() {
  global $data, $db, $result;

  $sql = "delete from pet_test_transport_detail where itemid = $data->id";
  $db->query($sql);

  $sql = "delete from pet_test_config where module = 'transport' and name = $data->id";
  $db->query($sql);

  foreach ($data->detail as $detail) {
    $sql = "insert into pet_test_config (module, name, value) values('transport', '$data->id', '$detail->name')";
    $db->query($sql);
  }
  
  $result['status'] = 1;
  $result['list'] = getlist();
  return $result; 
}

function getlist() {
  global $db, $data;

  $xtra = "";
  if (isset($data->{'keyword'})) $xtra = "and b.value like '%$data->keyword%'";
  $sql = "select a.* from pet_test_transport a inner join pet_test_config b on a.id = b.name $xtra group by a.id order by a.id desc";
  $pl = $db->all($sql);

  foreach ($pl as $i => $p) {
    $sql = "select value as name from pet_test_config where module = 'transport' and name = '$p[id]' order by id desc";
    $pl[$i]['detail'] = $db->all($sql);
    $pl[$i]['detailcover'] = implode(', ', $db->arr($sql, 'name'));
  }
  return $pl;
}
