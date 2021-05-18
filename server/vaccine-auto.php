<?php 

require_once(ROOTDIR .'/vaccine.php');
$vaccine = new Vaccine();

$filter = array(
  'status' => parseGetData('status', 0)
);

$result['status'] = 1;
$result['data'] = $vaccine->getList($filter);

echo json_encode($result);
die();
