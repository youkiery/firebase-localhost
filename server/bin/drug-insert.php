<?php 

require_once(ROOTDIR .'/drug.php');
$drug = new Drug();

$inputJSON = file_get_contents('php://input');
$data = json_decode($inputJSON);

if (empty($msg = $drug->insert($data))) {
  $result['status'] = 1;
  $result['data'] = $drug->filter2($data);
}
else {
  $result['messenger'] = $msg;
}
