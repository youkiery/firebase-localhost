<?php 

require_once(ROOTDIR .'/fivemin.php');
$fivemin = new Fivemin();

$filter = array(
  'nhanvien' => parseGetData('id', 0),
  'status' => parseGetData('status', 0),
  'time' => parseGetData('time', 0)
);

$result['status'] = 1;
$result['list'] = $fivemin->hoanthanh($filter);
