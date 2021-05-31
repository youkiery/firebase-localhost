<?php 

require_once(ROOTDIR .'/fivemin.php');
$fivemin = new Fivemin();

$filter = array(
  'start' => parseGetData('start', 0),
  'end' => parseGetData('end', 0),
);

$result['status'] = 1;
$result['list'] = $fivemin->thongke($filter);
