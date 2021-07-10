<?php 

$type = parseGetData('type', '');
$name = parseGetData('name', '');

$sql = "select * from pet_test_configv2 where name = '$type' and value = '$name'";
$query = $mysqli->query($sql);
$data = $query->fetch_assoc();

if (!empty($data)) {
  $result['messenger'] = 'Đã tồn tại';
}
else {
  $sql = "insert into pet_test_configv2 (name, value) values ('$type', '$name')";
  $query = $mysqli->query($sql);
  
  $list = array();
  $sql = 'select * from pet_test_configv2 where name = "'. $type .'" order by id asc';
  $query = $mysqli->query($sql);
  $index = 0;
  while ($row = $query->fetch_assoc()) {
    $list []= array(
      'id' => $index ++,
      'name' => $row['value']
    );
  }
  $result['list'] = $list;
  $result['status'] = 1;
}
