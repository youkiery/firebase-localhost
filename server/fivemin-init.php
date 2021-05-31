<?php 

require_once(ROOTDIR .'/fivemin.php');
$fivemin = new Fivemin();

$filter = array(
  'page' => parseGetData('page', 1)
);

$result['status'] = 1;
$result['list'] = $fivemin->init($filter);
