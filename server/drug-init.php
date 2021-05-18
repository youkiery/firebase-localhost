<?php 

require_once(ROOTDIR .'/drug.php');
$drug = new Drug();

$result['status'] = 1;
$result['role'] = $drug->check();
