<?php 

require_once(ROOTDIR .'/target.php');
$target = new Target();

$id = parseGetData('id', '');

$target->remove($id);
$result['status'] = 1;
$result['list'] = $target->init();
