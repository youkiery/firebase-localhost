<?php

$data = array(
  'customer' => parseGetData('customer', ''),
  'sampleid' => parseGetData('sampleid', ''),
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
  'samplestatus' => parseGetData('samplestatus', '')
);

$sql = 'select * from pet_test_target where active = 1 order by id asc';
$query = $mysqli->query($sql);
$list = array();
while ($row = $query->fetch_assoc()) {
  $list []= $row['id'];
  $data[$row['id']] = parseGetData($row['id'], 0);
}

$time = time() * 1000;
$sql = 'insert into pet_test_profile (customer, sampleid, address, name, weight, age, gender, type, serial, sampletype, samplenumber, samplesymbol, samplestatus, doctor, time) values ("'. $data['customer'] .'", "'. $data['sampleid']. '", "'. $data['address']. '", "'. $data['name']. '", "'. $data['weight']. '", "'. $data['age']. '", "'. $data['gender']. '", "'. $data['type']. '", "'. $data['serial']. '", "'. $data['sampletype']. '", "'. $data['samplenumber']. '", "'. $data['samplesymbol']. '", "'. $data['samplestatus']. '", "'. $userid. '", '. $time .')';
$mysqli->query($sql);
$id = $mysqli->insert_id;

foreach ($list as $key) {
  $sql = 'insert into pet_test_profile_data (pid, tid, value) values ('. $id .', '. $key .', "'. $data[$key] .'")';
  $mysqli->query($sql);
}

$serial = intval($data['serial']) + 1;
$sql = 'update pet_test_configv2 set value = "'. $serial .'" where name = "serial"';
$mysqli->query($sql);

$data = array(
  'id' => $id,
  'name' => $data['name'],
  'customer' => $data['customer'],
  'time' => $time
);

$result['status'] = 1;
$result['data'] = $data;
$result['serial'] = $serial;
