<?php 

require_once(ROOTDIR .'/expire.php');
$expire = new Expire();

$inputJSON = file_get_contents('php://input');
$data = json_decode($inputJSON);

if (!strlen($data->filter->ftime)) $data->filter->ftime = 7776000;

foreach ($data->list as $id) {
  $expire->remove($id);
}

$result['status'] = 1;
$result['list'] = $expire->getList();
$result['messenger'] = 'Đã xóa hạn sử dụng';
