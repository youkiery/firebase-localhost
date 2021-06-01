<?php 

require_once(ROOTDIR .'/fivemin.php');
$fivemin = new Fivemin();

$inputJSON = file_get_contents('php://input');
$data = json_decode($inputJSON);

$result['status'] = 1;
$fivemin->upload($data->rid, $data->image, $data->lydo, $data->hoanthanh);
$result['data'] = $fivemin->get($data->id);
