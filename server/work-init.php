<?php 

require_once(ROOTDIR .'/work.php');
$work = new Work();

$filter = array(
  'startdate' => ( !empty($_GET['startdate']) ? $_GET['startdate'] : '' ),
  'enddate' => ( !empty($_GET['enddate']) ? $_GET['enddate'] : '' ),
  'keyword' => ( !empty($_GET['keyword']) ? $_GET['keyword'] : '' ),
  'user' => ( !empty($_GET['user']) ? $_GET['user'] : '' ),
  'page1' => parseGetData('page1', 1),
  'page2' => parseGetData('page2', 1)
);

$result['list'] = $work->initList($filter);
$result['time'] = $work->getLastUpdate();
$result['unread'] = $work->getNotifyUnread();
$result['status'] = 1;
