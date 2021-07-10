<?php 

$name = parseGetData('name', '');

$sql = 'select * from `pet_test_storage_item` where name = "'. $name .'" limit 1';
$query = $mysqli->query($sql);

if (empty($row = $query->fetch_assoc())) {
  $sql = 'insert into `pet_test_storage_item` (code, name, storageid, number1, number2, transfer, purchase, position) values("", "'. $name .'", 0, 0, 0, 0, 0, "")';
  $mysqli->query($sql);
  $rid = $mysqli->insert_id;
}
else {
  $rid = $row['id'];
}

$result['status'] = 1;
$result['id'] = $rid;
