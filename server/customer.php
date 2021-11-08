<?php
function search() {
  global $data, $db, $result;

  $data->key = trim($data->key);
  $sql = "select * from pet_test_customer where phone like '%$data->key%' limit 20";
  $result['status'] = 1;
  $result['list'] = $db->all($sql);
  return $result;
}

function petsearch() {
  global $data, $db, $result;

  $data->key = trim($data->key);
  $sql = "select * from pet_test_customer where phone like '%$data->key%' limit 10";
  $list = $db->all($sql);

  foreach ($list as $key => $value) {
    $sql = "select id, name from pet_test_pet where customerid = $value[id]";
    $list[$key]['pet'] = $db->all($sql);
  }
  $result['status'] = 1;
  $result['list'] = $list;
  return $result;
}
