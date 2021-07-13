<?php 

require_once(ROOTDIR .'/drug.php');
$drug = new Drug();

$filter = array(
  'name' => parseGetData('key_name', ''),
  'effect' => parseGetData('key_effect', ''),
);

$result['status'] = 1;
$result['data'] = $drug->filter($filter);
