<?php 

require_once(ROOTDIR .'/expire.php');
$expire = new Expire();
$storage = $expire->storage();

$result['status'] = 1;
$result['list'] = $expire->item($storage);
$result['storage'] = $expire->storageList();
