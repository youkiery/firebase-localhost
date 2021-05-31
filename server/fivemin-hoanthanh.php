<?php 

require_once(ROOTDIR .'/fivemin.php');
$fivemin = new Fivemin();

$filter = array(
  'nhanvien' => parseGetData('id', 0),
  'status' => parseGetData('status', 0),
  'time' => parseGetData('time', 0),
  'page' => parseGetData('page', 1)
);

$result['status'] = 1;
$result['list'] = $fivemin->hoanthanh($filter);
