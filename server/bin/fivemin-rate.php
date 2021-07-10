<?php 

require_once(ROOTDIR .'/fivemin.php');
$fivemin = new Fivemin();

$inputJSON = file_get_contents('php://input');
$data = json_decode($inputJSON);

$data = $fivemin->rate($data);

$result['status'] = 1;
