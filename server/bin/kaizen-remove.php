<?php 

$filter = array(
  'keyword' => ( !empty($_GET['keyword']) ? $_GET['keyword'] : '' )
);

$data = array(
  'id' => parseGetData('id'),
);

$filter = array(
  'starttime' => parseGetData('starttime'),
  'endtime' => parseGetData('endtime'),
  'keyword' => parseGetData('keyword'),
  'page1' => parseGetData('page1', 1),
  'page2' => parseGetData('page2', 1),
  'type' => parseGetData('type', 0),
  'sort' => parseGetData('sort')
);

require_once(ROOTDIR .'/kaizen.php');
$kaizen = new Kaizen('test');

if (!$kaizen->role) $result['messenger'] = 'Chưa cấp quyền truy cập';
else {
  $result['status'] = 1;
  $result['time'] = $kaizen->removeData($data);
  $result['messenger'] = 'Đã xóa giải pháp';
  $result['list'] = $kaizen->initList();
  $result['unread'] = $kaizen->getNotifyUnread();
}

