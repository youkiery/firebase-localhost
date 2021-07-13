<?php 

require_once(ROOTDIR .'/kaizen.php');
$kaizen = new Kaizen();

$kaizen->setLastRead(time());
$result['status'] = 1;
$result['list'] = $kaizen->getKaizenNotify();
