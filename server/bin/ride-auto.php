<?php
$filter = array(
  'time' => parseGetData('time', time()),
  'type' => parseGetData('type', 0)
);

require_once(ROOTDIR .'/ride.php');
$ride = new Ride();

$result['status'] = 1;
$result['list'] = $ride->getList($filter);

