<?php 

require_once(ROOTDIR .'/fivemin.php');
$fivemin = new Fivemin();

$inputJSON = file_get_contents('php://input');
$data = json_decode($inputJSON);

$fivemin->update($data->danhsach, $data->id);
$result['status'] = 1;
$result['get'] = $fivemin->get($data->id);
