<?php 

require_once(ROOTDIR .'/work.php');
$work = new Work();

$result['status'] = 1;
$result['notify'] = $work->getUserNotify();
$work->setLastRead(time());

echo json_encode($result);
die();
