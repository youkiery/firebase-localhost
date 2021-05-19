<?php

$data = array(
  'work' => parseGetData('work', 0),
  'kaizen' => parseGetData('kaizen', 0),
  'schedule' => parseGetData('schedule', 0),
  'vaccine' => parseGetData('vaccine', 0),
  'spa' => parseGetData('spa', 0),
  'expire' => parseGetData('expire', 0),
  'blood' => parseGetData('blood', 0),
  'usg' => parseGetData('usg', 0),
  'drug' => parseGetData('drug', 0),
  'target' => parseGetData('target', 0),
  'profile' => parseGetData('profile', 0),
);
$id = parseGetData('id', 0);

foreach ($data as $key => $value) {
  $sql = 'select * from pet_test_permission where module = "'. $key .'" and userid = '. $id;
  $query = $mysqli->query($sql);
  $type = intval($value);

  if (empty($query->fetch_assoc())) {
    $sql = 'insert into pet_test_permission (userid, module, type) values ('. $id .', "'. $key .'", '. $type .')';
  }
  else {
    $sql = 'update pet_test_permission set type = '. $type .' where module = "'. $key .'" and userid = '. $id;
  }
  $query = $mysqli->query($sql);
}

$result['status'] = 1;
$result['data'] = $data;
