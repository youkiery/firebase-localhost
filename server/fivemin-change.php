<?php 

require_once(ROOTDIR .'/fivemin.php');
$fivemin = new Fivemin();

$id = parseGetData('id', 0);
$rid = parseGetData('rid', 0);
$status = parseGetData('status', 0);

$result['status'] = 1;
$fivemin->change($rid, $status, $image);
$result['data'] = $fivemin->get($id);
