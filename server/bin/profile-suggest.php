<?php 

$key = parseGetData('key', '');

$list = array();

$sql = 'select * from pet_test_profile_customer where name like "%'. $key .'%" or phone like "%'. $key .'%" limit 20';
$query = $mysqli->query($sql);

while ($row = $query->fetch_assoc()) {
  $list []= array(
    'name' => $row['name'],
    'phone' => $row['phone'],
    'address' => $row['address'],
  );
}

$result['status'] = 1;
$result['list'] = $list;
