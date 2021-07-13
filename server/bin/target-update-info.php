<?php

require_once(ROOTDIR .'/target.php');
$target = new Target();

$data = array(
  'id' => parseGetData('id', ''),
  'name' => parseGetData('name', ''),
  'intro' => parseGetData('intro', ''),
  'unit' => parseGetData('unit', ''),
  'flag' => parseGetData('flag', ''),
  'up' => parseGetData('up', ''),
  'down' => parseGetData('down', ''),
  'disease' => parseGetData('disease', ''),
  'aim' => parseGetData('aim', ''),
);
$key = parseGetData('key', '');

$sql = "update pet_test_target set name = '$data[name]', intro = '$data[intro]', unit = '$data[unit]', flag = '$data[flag]', up = '$data[up]', down = '$data[down]', disease = '$data[disease]', aim = '$data[aim]' where id = ". $data['id'];
$mysqli->query($sql);

$result['status'] = 1;
$result['list'] = $target->init($key);
