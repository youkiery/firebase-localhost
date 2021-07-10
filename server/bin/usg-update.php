<?php 

require_once(ROOTDIR .'/usg.php');
$usg = new Usg();

$data = array(
  'id' => parseGetData('id'),
  'number' => parseGetData('number'),
  'calltime' => parseGetData('calltime'),
);
$data['calltime'] = totime($data['calltime']);

$sql = "update `pet_test_usg2` set expecttime = $data[calltime], number = $data[number] where id = $data[id]";
$mysqli->query($sql);

$start = strtotime(date('Y/m/d'));
$end = time();

$sql = 'select * from pet_test_usg2 where (time between '. $start . ' and '. $end . ') and status = 0 limit 50';
$query = $mysqli->query($sql);

$data = array();
// tên thú cưng, sđt, vaccine, ngày tái chủng, ghi chú, trạng thại
while ($row = $query->fetch_assoc()) {
  $pet = $usg->getPetId($row['petid']);
  $customer = $usg->getCustonerId($pet['customerid']);
  if (!empty($customer['phone'])) {
    $data []= array(
      'id' => $row['id'],
      'name' => $customer['name'],
      'number' => $customer['phone'],
      'birth' => $row['number'],
      'calltime' => date('d/m/Y', $row['expecttime']),
    );
  }
}

$result['status'] = 1;
$result['new'] = $data;
