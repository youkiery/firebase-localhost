<?php
require_once(ROOTDIR .'/ride.php');
$ride = new Ride();

$filter = array(
  'time' => parseGetData('time', time()),
  'type' => parseGetData('type', 0)
);

$id = parseGetData('id', 0);
$sql = "delete from `" . $ride->prefix . "` where id = " . $id;

$ride->db->query($sql);
$result['status'] = 1;
$result['list'] = $ride->getList($filter);

