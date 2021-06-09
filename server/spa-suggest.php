<?php 

require_once(ROOTDIR .'/spa.php');
$spa = new spa();

$filter = array(
  'value' => parseGetData('value', '')
);

$data = array();

$sql = 'select * from `pet_test_customer` where name like "%'. $filter['value'] .'%" or phone like "%'. $filter['value'] .'%" limit 20';
$query = $spa->db->query($sql);

while ($row = $query->fetch_assoc()) {
  $data []= array(
    'name' => $row['name'],
    'phone' => $row['phone'],
  );
}

$result['status'] = 1;
$result['data'] = $data;
