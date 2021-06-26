<?php 

require_once(ROOTDIR .'/spa.php');
$spa = new Spa();

$data = array(
  'id' => parseGetData('id', '0'),
);

$time = time();

$sql = 'select done from pet_test_spa where id = ' . $data['id'];
$query = $mysqli->query($sql);
$row = $query->fetch_assoc();

if ($row['done']) $sql = 'update pet_test_spa set done = 0 where id = ' . $data['id'];
else $sql = 'update pet_test_spa set done = '. $time .' where id = ' . $data['id'];
$mysqli->query($sql);
$spa->setLastUpdate($time);

$result['status'] = 1;
$result['data'] = $spa->getList($time);
$result['time'] = $time;
