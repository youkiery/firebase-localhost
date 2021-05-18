<?php 

$filter = array(
  'time' => parseGetData('time'),
  'starttime' => parseGetData('starttime'),
  'endtime' => parseGetData('endtime'),
  'keyword' => parseGetData('keyword'),
  'page1' => parseGetData('page1', 1),
  'page2' => parseGetData('page2', 1),
  'auto' => parseGetData('auto', 1),
  'sort' => parseGetData('sort')
);
$filter['time'] = intval($filter['time']);

require_once(ROOTDIR .'/kaizen.php');
$kaizen = new Kaizen();

$result['status'] = 1;
$result['list'] = $kaizen->initList();
$result['unread'] = $kaizen->getNotifyUnread(); 
