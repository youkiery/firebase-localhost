<?php 

$filter = array(
  'keyword' => ( !empty($_GET['keyword']) ? $_GET['keyword'] : '' )
);

$data = array(
  'problem' => parseGetData('problem'),
  'solution' => parseGetData('solution'),
  'result' => parseGetData('result')
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

$result['time'] = time();
$kaizen->insertData($data, $result['time']);

$result['status'] = 1;
$result['list'] = $kaizen->initList();
$result['messenger'] = 'Đã thêm giải pháp';
$result['unread'] = $kaizen->getNotifyUnread();
