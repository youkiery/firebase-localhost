<?php 

require_once(ROOTDIR .'/fivemin.php');
$fivemin = new Fivemin();

$filter = array(
  'time' => parseGetData('time', 0),
  'page' => parseGetData('page', 0),
  'id' => parseGetData('id', 0),
);

$result['status'] = 1;
$fivemin->remove($filter['id']);
$result['list'] = $fivemin->init($filter);
