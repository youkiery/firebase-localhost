<?php 

require_once(ROOTDIR .'/drug.php');
$drug = new Drug();

$filter = array(
  'name' => parseGetData('key_name', ''),
  'effect' => parseGetData('key_effect', ''),
);
$data = array(
  'id' => parseGetData('id', ''),
  'name' => parseGetData('name', ''),
  'sideeffect' => parseGetData('sideeffect', ''),
  'mechanic' => parseGetData('mechanic', ''),
  'limit' => parseGetData('limit', ''),
  'effect' => parseGetData('effect', '')
);

if (empty($msg = $drug->update($data))) {
  $result['status'] = 1;
  $result['data'] = $drug->select($data['id']);
}
else {
  $result['messenger'] = $msg;
}
