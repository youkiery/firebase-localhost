<?php 

require_once(ROOTDIR .'/blood.php');
$blood = new Blood();

$filter = array(
  'page' => parseGetData('page', 1)
);

$result['status'] = 1;
$result['list'] = $blood->getList();
