<?php

$data = array(
  'customer' => parseGetData('customer', ''),
  'phone' => parseGetData('phone', ''),
  'address' => parseGetData('address', ''),
  'name' => parseGetData('name', ''),
  'weight' => parseGetData('weight', ''),
  'age' => parseGetData('age', ''),
  'gender' => parseGetData('gender', ''),
  'type' => parseGetData('type', ''),
  'serial' => parseGetData('serial', ''),
  'sampletype' => parseGetData('sampletype', ''),
  'samplenumber' => parseGetData('samplenumber', ''),
  'samplesymbol' => parseGetData('samplesymbol', ''),
  'samplestatus' => parseGetData('samplestatus', ''),
  'symptom' => parseGetData('symptom', ''),
);

$sql = 'select * from pet_test_profile_customer where phone = "'. $data['phone'] .'"';
$query = $mysqli->query($sql);
$customer = $query->fetch_assoc();

if (empty($customer)) {
  $sql = "insert into pet_test_profile_customer (name, phone, address) values('$data[customer]', '$data[phone]', '$data[address]')";
  $mysqli->query($sql);
  $customerid = $mysqli->insert_id;
}
else {
  $sql = "update pet_test_profile_customer set name = '$data[customer]', address = '$data[address]' where phone = ". $data['phone'];
  $mysqli->query($sql);
  $customerid = $customer['id'];
}

$sql = 'select * from pet_test_target where active = 1 order by id asc';
$query = $mysqli->query($sql);
$list = array();
while ($row = $query->fetch_assoc()) {
  $list []= $row['id'];
  $data[$row['id']] = parseGetData($row['id'], 0);
}

$time = time() * 1000;
$sql = 'insert into pet_test_profile (customer, phone, address, name, weight, age, gender, type, serial, sampletype, samplenumber, samplesymbol, samplestatus, symptom, doctor, time) values ("'. $data['customer'] .'", "'. $data['phone'] .'", "'. $data['address'] .'", "'. $data['name']. '", "'. $data['weight']. '", "'. $data['age']. '", "'. $data['gender']. '", "'. $data['type']. '", "'. $data['serial']. '", "'. $data['sampletype']. '", "'. $data['samplenumber']. '", "'. $data['samplesymbol']. '", "'. $data['samplestatus']. '", "'. $data['symptom'] .'", "'. $userid. '", '. $time .')';
$mysqli->query($sql);
$id = $mysqli->insert_id;

foreach ($list as $key) {
  $sql = 'insert into pet_test_profile_data (pid, tid, value) values ('. $id .', '. $key .', "'. $data[$key] .'")';
  $mysqli->query($sql);
}

$serial = floatval($data['serial']) + 1;
$sql = 'select * from pet_test_configv2 where name = "serial"';
$query = $mysqli->query($sql);
$config = $query->fetch_assoc();
if (empty($config)) {
  $sql = 'insert into pet_test_configv2 (name, value) values("serial", "'. $serial .'")';
}
else {
  $sql = 'update pet_test_configv2 set value = "'. $serial .'" where name = "serial"';
}
$mysqli->query($sql);

$data = array(
  'id' => $id,
  'name' => $data['name'],
  'customer' => $data['customer'],
  'phone' => $data['phone'],
  'time' => $time
);

$result['status'] = 1;
$result['data'] = $data;
$result['serial'] = $serial;
