<?php 

require_once(ROOTDIR .'/usg.php');
$usg = new Usg();

$filter = array(
  'value' => parseGetData('value', '')
);

$data = array();

$sql = 'select * from `pet_'. $usg->table .'_customer` where name like "%'. $filter['value'] .'%" or phone like "%'. $filter['value'] .'%" limit 20';
$query = $usg->db->query($sql);

while ($row = $query->fetch_assoc()) {
  $sql = 'select * from `pet_'. $usg->table .'_pet` where customerid = ' . $row['id'];
  $query2 = $usg->db->query($sql);
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
