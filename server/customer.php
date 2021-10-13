<?php
function search() {
  global $data, $db, $result;

  $data->key = trim($data->key);
  $sql = "select * from pet_test_customer where phone like '%$data->key%' limit 20";
  $result['status'] = 1;
  $result['list'] = $db->all($sql);
  return $result;
}
