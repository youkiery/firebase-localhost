<?php 

require_once(ROOTDIR .'/fivemin.php');
$fivemin = new Fivemin();

$inputJSON = file_get_contents('php://input');
$data = json_decode($inputJSON);

$status = $fivemin->upload($data->rid, $data->image, $data->lydo, $data->hoanthanh);
if ($status) {
  $result['status'] = 1;
  $result['data'] = $fivemin->get($data->id);
} 
else $result['messenger'] = 'Đã hết thời gian cập nhật >.<';
