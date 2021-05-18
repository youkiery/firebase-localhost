<?php 

require_once(ROOTDIR .'/spa.php');
$spa = new Spa();

$result['status'] = 1;
$time = parseGetData('time', 0);
$current = parseGetData('current', 0);

$current /= 1000;

$time = strtotime(date('Y/m/d'));
$end = $time + 60 * 60 * 24 - 1;

if ($current <= $time || $current > $end || $spa->checkLastUpdate($time)) {
  $result['data'] = $spa->getList($current);
  $result['time'] = $spa->getLastUpdate();
}
