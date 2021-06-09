<?php 

$data = array(
  'id' => parseGetData('id'),
  'rid' => parseGetData('rid'),
  'number' => parseGetData('number'),
  'expire' => parseGetData('expire')
);
$data['expire'] = totime($data['expire']);

require_once(ROOTDIR .'/expire.php');
$expire = new Expire();

if (empty($data['id'])) {
  $sql = 'insert into `pet_test_storage_expire` (itemid, number, time, status) values ('. $data['rid'] .', '. $data['number'] .', '. $data['expire'] .', 0)';
}
else {
  $sql = 'update `pet_test_storage_expire` set rid = '. $data['rid'] .', number = '. $data['number'] .', time = '. $data['expire'] .' where id = '. $data['id'];
}
$expire->db->query($sql);

$result['status'] = 1;
$result['list'] = $expire->getList();
$result['messenger'] = 'Đã thêm hạn sử dụng';
