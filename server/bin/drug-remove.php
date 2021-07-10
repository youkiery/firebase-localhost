<?php 

require_once(ROOTDIR .'/drug.php');
$drug = new Drug();

$filter = array(
  'name' => parseGetData('key_name', ''),
  'effect' => parseGetData('key_effect', ''),
);
$id = parseGetData('id', '');

$drug->remove($id);
$result['status'] = 1;
$result['data'] = $drug->filter($filter);
