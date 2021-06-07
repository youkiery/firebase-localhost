<?php 

require_once(ROOTDIR .'/expire.php');
$expire = new Expire();

$result['status'] = 1;
$result['list'] = $expire->getList();
