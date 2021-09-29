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
  'profile' => parseGetData('profile', 0),
);
$id = parseGetData('id', 0);

foreach ($data as $key => $value) {
  $sql = 'select * from pet_test_user_per where module = "'. $key .'" and userid = '. $id;
  $query = $mysqli->query($sql);
  $type = intval($value);

  if (empty($query->fetch_assoc())) {
    $sql = 'insert into pet_test_user_per (userid, module, type) values ('. $id .', "'. $key .'", '. $type .')';
  }
  else {
    $sql = 'update pet_test_user_per set type = '. $type .' where module = "'. $key .'" and userid = '. $id;
  }
  $query = $mysqli->query($sql);
}

$module = array(
  'work' => 0,
  'kaizen' => 0,
  'schedule' => 0,
  'vaccine' => 0,
  'spa' => 0,
  'expire' => 0,
  'blood' => 0,
  'usg' => 0,
  'drug' => 0,
  'profile' => 0,
);

$sql = 'select * from pet_test_user_per where userid = '. $userid;
$query = $mysqli->query($sql);
$list = array();
while ($row = $query->fetch_assoc()) {
  $list[$row['module']] = $row['type'];
}

$result['status'] = 1;
$result['data'] = $data;
$result['config'] = $list;
