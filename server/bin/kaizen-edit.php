<?php 

$filter = array(
  'starttime' => parseGetData('starttime'),
  'endtime' => parseGetData('endtime'),
  'keyword' => parseGetData('keyword'),
  'page1' => parseGetData('page1', 1),
  'page2' => parseGetData('page2', 1),
  'type' => parseGetData('type', 0),
  'sort' => parseGetData('sort')
);

$data = array(
  'id' => parseGetData('id'),
  'problem' => parseGetData('problem'),
  'solution' => parseGetData('solution'),
  'result' => parseGetData('result')
);

require_once(ROOTDIR .'/kaizen.php');
$kaizen = new Kaizen();

$result['status'] = 1;
$result['time'] = $kaizen->updateData($data);
$result['list'] = $kaizen->initList();
$result['unread'] = $kaizen->getNotifyUnread();
