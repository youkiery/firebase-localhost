<?php 

require_once(ROOTDIR .'/fivemin.php');
$fivemin = new Fivemin();

$id = parseGetData('id', 0);

$result['status'] = 1;
$result['data'] = $fivemin->get($id);
