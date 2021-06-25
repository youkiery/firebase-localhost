<?php 

require_once(ROOTDIR .'/vaccine.php');
$vaccine = new Vaccine();

$data = array(
  'id' => parseGetData('id'),
  'disease' => parseGetData('disease'),
  'calltime' => parseGetData('calltime')
);
$data['calltime'] = totime($data['calltime']);

$sql = 'update `pet_test_vaccine` set calltime = '. $data['calltime'] .', diseaseid = '. $data['disease'] .' where id = '. $data['id'];
$mysqli->query($sql);

$start = strtotime(date('Y/m/d'));
$end = time();

$sql = 'select * from pet_test_vaccine where (ctime between '. $start . ' and '. $end . ') and status = 0 limit 50';
$query = $mysqli->query($sql);
$list = array();

$disease = $vaccine->getDiseaseList();

while ($row = $query->fetch_assoc()) {
  $pet = $vaccine->getPetId($row['petid']);
  $customer = $vaccine->getCustonerId($pet['customerid']);
  if (!empty($customer['phone'])) {
    $list []= array(
      'id' => $row['id'],
      'name' => $customer['name'],
      'number' => $customer['phone'],
      'vaccine' => $disease[$row['diseaseid']],
      'calltime' => $row['calltime'] * 1000,
    );
  }
}

$result['status'] = 1;
$result['new'] = $list;
