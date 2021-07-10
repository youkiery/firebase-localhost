<?php 

require_once(ROOTDIR .'/expire.php');
$expire = new Expire();

$name = parseGetData('name', '');

$expire->insertStorage($name);
$result['status'] = 1;
$result['list'] = $expire->storageList();
