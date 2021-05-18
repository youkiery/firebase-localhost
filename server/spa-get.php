<?php 

require_once(ROOTDIR .'/spa.php');
$spa = new Spa();

$data = array(
  'id' => parseGetData('id', '0'),
);

$sql = 'select * from `'. $spa->prefix .'` where id = ' . $data['id'];
$query = $mysqli->query($sql);
$row = $query->fetch_assoc();

$customer = $spa->getCustonerId($row['customerid']);
$types = $spa->getTypeList();

$type = explode(',', $row['type']);

if (count($type)) {
  foreach ($types as $index => $item) {
    if (array_search($item['id'], $type) !== false) {
      $types[$index]['value'] = 1;
    }
  }
}

$result['status'] = 1;
$result['data'] = array(
  'id' => $row['id'],
  'name' => $customer['name'],
  'phone' => $customer['phone'],
  'note' => $row['note'],
  'image' => explode(',', $row['image']),
  'type' => $types
);
