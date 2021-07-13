<?php 

require_once(ROOTDIR .'/fivemin.php');
$fivemin = new Fivemin();

$inputJSON = file_get_contents('php://input');
$data = json_decode($inputJSON);

$data = $fivemin->insert($data);

$result['status'] = 1;
$result['data'] = $data;
$result['get'] = $fivemin->get($data['id']);
$result['gopy'] = array(
  'gopy' => '',
  'nguoigopy' => ''
);
