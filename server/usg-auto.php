<?php 

require_once(ROOTDIR .'/usg.php');
$usg = new Usg();

$filter = array(
  'status' => parseGetData('status', 0),
  'keyword' => parseGetData('keyword', '')
);

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
      'name' => $row['name'],
      'number' => $row['phone'],
      'time' => date('d/m/Y', $row['usgtime']),
      'calltime' => date('d/m/Y', $row['expecttime']),
      'note' => $row['note'],
      'color' => $row['color'],
    );
  }
}

$result['status'] = 1;
$result['data'] = $usg->getList($filter);
$result['new'] = $data;
