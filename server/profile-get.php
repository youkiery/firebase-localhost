<?php 

$id = parseGetData('id', '');

$sql = 'select * from pet_test_profile where id = '. $id;
$query = $mysqli->query($sql);
$data = $query->fetch_assoc();

$sql = 'select a.value, b.name from pet_test_profile_data a inner join pet_test_target b on a.pid = '. $data['id'] .' and a.tid = b.id';
$query = $mysqli->query($sql);
$data['target'] = array();
while ($row = $query->fetch_assoc()) {
  $data['target'] []= array(
    'name' => $row['name'],
    'value' => $row['value']
  );
}

$sql = 'select value from pet_test_configv2 where name = "type" limit 1 offset '. $data['type'];
$query = $mysqli->query($sql);
$row = $query->fetch_assoc();
$data['type'] = $row['value'];

$sql = 'select value from pet_test_configv2 where name = "sampletype" limit 1 offset '. $data['sampletype'];
$query = $mysqli->query($sql);
$row = $query->fetch_assoc();
$data['sampletype'] = $row['value'];

$sql = 'select * from pet_users where userid = '. $data['doctor'];
$query = $mysqli->query($sql);
$doctor = $query->fetch_assoc();

$data['doctor'] = $doctor['last_name'] . ' '. $doctor['first_name'];

$result['status'] = 1;
$result['data'] = $data;
