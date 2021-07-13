<?php 

require_once(ROOTDIR .'/vaccine.php');
$vaccine = new Vaccine();

$keyword = parseGetData('keyword', '');

$data = array();
$disease = $vaccine->getDiseaseList();

$sql = 'select b.name as petname, c.phone, a.diseaseid, a.calltime, a.note, a.note, a.status from `pet_test_vaccine` a inner join `pet_test_pet` b on a.petid = b.id inner join `pet_test_customer` c on b.customerid = c.id where c.name like "%'. $keyword .'%" or c.phone like "%'. $keyword .'%" order by a.calltime';
$query = $mysqli->query($sql);

// tên thú cưng, sđt, vaccine, ngày tái chủng, ghi chú, trạng thại
while ($row = $query->fetch_assoc()) {
  $data []= array(
    'petname' => $row['petname'],
    'number' => $row['phone'],
    'vaccine' => $disease[$row['diseaseid']],
    'calltime' => $row['calltime'],
    'note' => $row['note'],
    'status' => $row['status'],
  );
}

$result['status'] = 1;
$result['data'] = $data;

echo json_encode($result);
die();
