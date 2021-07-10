<?php 

require_once(ROOTDIR .'/expire.php');
$expire = new Expire();

$inputJSON = file_get_contents('php://input');
$data = json_decode($inputJSON);

$id = $expire->insertItem($data);
$result['status'] = 1;
$result['data'] = $expire->getItemId($id);
