<?php 

require_once(ROOTDIR .'/target.php');
$target = new Target();

// $id = parseGetData('value', '');

$result['status'] = 1;
$result['list'] = $target->init();
