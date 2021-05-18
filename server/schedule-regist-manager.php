<?php 

require_once(ROOTDIR .'/schedule.php');
$schedule = new Schedule();
// ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ nhật']
$reversal_day = array(
  0 => 1, 2, 3, 4, 5, 6, 7 
);

$list = json_decode(parseGetData('list'));
$filter = array(
  'time' => parseGetData('time') / 1000
);

foreach ($list as $value) {
  // a => day, b => type
  $time = $filter['time'] + 60 * 60 * 24 * ($value->day - date('N', $filter['time']) + 1);
  $schedule->insert($value->userid, $time, $value->type, $value->color);
}

$result['status'] = 1;
$result['messenger'] = 'Đã đăng ký lịch';
$result['data'] = $schedule->getList($filter);;
