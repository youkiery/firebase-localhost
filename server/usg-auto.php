<?php 

require_once(ROOTDIR .'/usg.php');
$usg = new Usg();

$filter = array(
  'status' => parseGetData('status', 0),
  'keyword' => parseGetData('keyword', '')
);

$result['status'] = 1;
$result['data'] = $usg->getList($filter);

echo json_encode($result);
die();
