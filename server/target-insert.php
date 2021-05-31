<?php 

require_once(ROOTDIR .'/target.php');
$target = new Target();

$data = array(
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

$msg = $target->insert($data);
$result['status'] = 1;
$result['list'] = $target->init($key);
