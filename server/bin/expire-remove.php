<?php 

$id = parseGetData('id', '');

require_once(ROOTDIR .'/expire.php');
$expire = new Expire();

$expire->remove($id);
$result['status'] = 1;
$result['list'] = $expire->getList();
$result['messenger'] = 'Đã xoas hạn sử dụng';
