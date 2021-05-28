<?php 

require_once(ROOTDIR .'/fivemin.php');
$fivemin = new Fivemin();

$filter = array(
  'time' => parseGetData('time', 0)
);

$result['status'] = 1;
$result['list'] = $fivemin->init($filter);
