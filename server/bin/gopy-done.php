<?php 

$id = parseGetData('id', '0');

$sql = 'update pet_test_gopy set trangthai = 1 where id = '. $id;
$mysqli->query($sql);

$list = array();
$sql = 'select * from pet_test_gopy where trangthai = 0 order by id desc';
$query = $mysqli->query($sql);

while($row = $query->fetch_assoc()) {
  $list []= $row;
}

$result['status'] = 1;
$result['list'] = $list;
