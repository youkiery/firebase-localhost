<?php 

require_once(ROOTDIR .'/work.php');
$work = new Work();
if (!$work->role) $result['messenger'] = 'Chưa cấp quyền truy cập';
else {
  $data = array(
    'content' => parseGetData('content'),
    'cometime' => totime(parseGetData('cometime')),
    'calltime' => totime(parseGetData('calltime')),
    'employ' => parseGetData('employ')
  );

  $filter = array(
    'startdate' => ( !empty($_GET['startdate']) ? $_GET['startdate'] : '' ),
    'enddate' => ( !empty($_GET['enddate']) ? $_GET['enddate'] : '' ),
    'keyword' => ( !empty($_GET['keyword']) ? $_GET['keyword'] : '' ),
    'user' => ( !empty($_GET['user']) ? $_GET['user'] : '' ),
    'page1' => parseGetData('page1', 1),
    'page2' => parseGetData('page2', 1),
    'status' => parseGetData('status', 0)
  );

  $time = time();
  $work->insertWork($data, $time);
  $result['status'] = 1;
  $result['messenger'] = 'Đã thêm công việc';
  $result['time'] = $time;
  $result['unread'] = $work->getNotifyUnread();
  $result['list'] = $work->initList($filter);
}
