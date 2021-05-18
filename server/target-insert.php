<?php 

require_once(ROOTDIR .'/target.php');
$target = new Target();

$name = parseGetData('name', '');

$msg = $target->insert($name);
if (empty($msg)) $result['messenger'] = $msg;
else {
  $result['status'] = 1;
  $result['list'] = $target->init();
}
