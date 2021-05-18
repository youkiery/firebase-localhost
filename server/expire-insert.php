<?php 

$data = array(
  'id' => parseGetData('id'),
  'rid' => parseGetData('rid'),
  'number' => parseGetData('number'),
  'expire' => parseGetData('expire')
);
$data['expire'] = totime($data['expire']);
$filter = array(
  'ftime' => parseGetData('ftime', 7776000),
  'fname' => parseGetData('fname', '')
);

require_once(ROOTDIR .'/expire.php');
$expire = new Expire();

if (empty($data['id'])) {
  $sql = 'insert into `'. $expire->prefix .'` (rid, number, exp_time, update_time) values ('. $data['rid'] .', '. $data['number'] .', '. $data['expire'] .', '. time() .')';
}
else {
  $sql = 'update `'. $expire->prefix .'` set rid = '. $data['rid'] .', number = '. $data['number'] .', exp_time = '. $data['expire'] .' where id = '. $data['id'];
}
$expire->db->query($sql);

$result['status'] = 1;
$result['list'] = $expire->getList($filter);
$result['messenger'] = 'Đã thêm hạn sử dụng';
