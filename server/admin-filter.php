<?php 

$inputJSON = file_get_contents('php://input');
$data = json_decode($inputJSON);

$sql = 'select * from pet_test_user';
$query = $mysqli->query($sql);
$list = array();

while ($row = $query->fetch_assoc()) $list []= $row['userid'];

$sql = 'select * from pet_users where userid not in ('. implode(', ', $list) .') and (username like "%'. $data->key .'%" or first_name like "%'. $data->key .'%" or last_name like "%'. $data->key .'%")';
$query = $mysqli->query($sql);
$list = array();

while ($row = $query->fetch_assoc()) $list []= array(
  'id' => $row['userid'],
  'fullname' => $row['last_name'] . ' '. $row['first_name'],
  'username' => $row['username']
);

$result['status'] = 1;
$result['list'] = $list;
