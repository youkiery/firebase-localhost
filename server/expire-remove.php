<?php 

$id = parseGetData('id', '');
$filter = array(
  'ftime' => parseGetData('ftime', 7776000),
  'fname' => parseGetData('fname', '')
);

require_once(ROOTDIR .'/expire.php');
$expire = new Expire();

$sql = 'delete from `'. $expire->prefix .'` where id = '. $id;
$expire->db->query($sql);

$result['status'] = 1;
$result['list'] = $expire->getList($filter);
$result['messenger'] = 'Đã xoas hạn sử dụng';
