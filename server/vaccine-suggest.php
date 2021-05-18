<?php 

require_once(ROOTDIR .'/vaccine.php');
$vaccine = new Vaccine();

$filter = array(
  'value' => parseGetData('value', '')
);

$data = array();

$sql = 'select * from `pet_'. $vaccine->table .'_customer` where name like "%'. $filter['value'] .'%" or phone like "%'. $filter['value'] .'%" limit 20';
$query = $vaccine->db->query($sql);

while ($row = $query->fetch_assoc()) {
  $sql = 'select * from `pet_'. $vaccine->table .'_pet` where customerid = ' . $row['id'];
  $query2 = $vaccine->db->query($sql);
  $list = array();
  while ($row2 = $query2->fetch_assoc()) {
    $list []= array(
      'id' => $row2['id'],
      'name' => $row2['name'],
    );
  }
  $data []= array(
    'name' => $row['name'],
    'phone' => $row['phone'],
    'pet' => json_encode($list)
  );
}

$result['status'] = 1;
$result['data'] = $data;

echo json_encode($result);
die();
