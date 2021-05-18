<?php 

require_once(ROOTDIR .'/usg.php');
$usg = new Usg();

$id = parseGetData('id', 0);
$number = parseGetData('number', 0);

$filter = array(
  'status' => parseGetData('status', 0),
  'keyword' => parseGetData('keyword', '')
);

$sql = 'update `'. $usg->prefix .'` set expectnumber = ' . $number . ', status = 2 where id = ' . $id;
// die($sql);
$mysqli->query($sql);

$result['status'] = 1;
$result['data'] = $usg->getList($filter);

echo json_encode($result);
die();
