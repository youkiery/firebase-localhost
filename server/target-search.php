<?php 

require_once(ROOTDIR .'/target.php');
$target = new Target();

$key = parseGetData('key', '');

$result['status'] = 1;
$result['list'] = $target->init($key);
