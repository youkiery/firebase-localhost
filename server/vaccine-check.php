<?php 

require_once(ROOTDIR .'/vaccine.php');
$vaccine = new Vaccine();

$reversal = array(
  '0' => '1',
  '1' => '0'
);
$id = parseGetData('id', 0);

$filter = array(
  'status' => parseGetData('status', 0)
);

$sql = 'update `'. $vaccine->prefix .'` set status = ' . $reversal[$filter['status']] . ' where id = ' . $id;
$mysqli->query($sql);

$result['status'] = 1;
$result['data'] = $vaccine->getList($filter);

echo json_encode($result);
die();
