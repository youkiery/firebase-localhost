<?php 

require_once(ROOTDIR .'/drug.php');
$drug = new Drug();

$filter = array(
  'name' => parseGetData('key_name', ''),
  'effect' => parseGetData('key_effect', ''),
);
$data = array(
  'code' => parseGetData('code', ''),
  'name' => parseGetData('name', ''),
  'sideeffect' => parseGetData('sideeffect', ''),
  'mechanic' => parseGetData('mechanic', ''),
  'limit' => parseGetData('limit', ''),
  'effect' => parseGetData('effect', '')
);

if (empty($msg = $drug->insert($data))) {
  $result['status'] = 1;
  $result['data'] = $drug->filter($filter);
}
else {
  $result['messenger'] = $msg;
}
