<?php 

require_once(ROOTDIR .'/target.php');
$target = new Target();

$id = parseGetData('id', '');

$result['status'] = 1;
$target->update($id);
