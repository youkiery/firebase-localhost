<?php 

require_once(ROOTDIR .'/usg.php');
$usg = new Usg();

$reversal = array(
  '0' => '1',
  '1' => '0'
);
$id = parseGetData('id', 0);

$filter = array(
  'status' => parseGetData('status', 0),
  'keyword' => parseGetData('keyword', '')
);

$sql = 'update `'. $usg->prefix .'` set status = ' . $reversal[$filter['status']] . ' where id = ' . $id;
$mysqli->query($sql);

$result['status'] = 1;
$result['data'] = $usg->getList($filter);

echo json_encode($result);
die();
