<?php 

$filter = array(
  'ftime' => parseGetData('ftime', 7776000),
  'fname' => parseGetData('fname', '')
);
// $filter['time'] = intval($filter['time']);

require_once(ROOTDIR .'/expire.php');
$expire = new Expire();

$result['status'] = 1;
$result['list'] = $expire->getList($filter);
