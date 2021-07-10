<?php 
$inputJSON = file_get_contents('php://input');
$data = json_decode($inputJSON);

$sql = 'select * from pet_test_user where userid = '. $data->id;
$query = $mysqli->query($sql);

if (!empty($row = $query->fetch_assoc())) {
  $result['status'] = 1;
  $result['messenger'] = 'Nhân viên đã tồn tại';
}
else {
  $sql = "insert into pet_test_user (userid, manager, except, daily, kaizen) values($data->id, 0, 0, 0, 0)";
  $mysqli->query($sql);

  $result['status'] = 1;
  $result['messenger'] = 'Đã thêm nhân viên';
}

$sql = 'select * from pet_test_user';
$query = $mysqli->query($sql);
$list = array();

while ($row = $query->fetch_assoc()) $list []= $row['userid'];

$sql = 'select * from pet_users where userid not in ('. implode(', ', $list) .') and (username like "%'. $data->key .'%" or first_name like "%'. $data->key .'%" or last_name like "%'. $data->key .'%")';
$query = $mysqli->query($sql);
$list = array();

while ($row = $query->fetch_assoc()) $list []= array(
  'id' => $row['userid'],
  'fullname' => $row['last_name'] . ' '. $row['first_name'],
  'username' => $row['username']
);
$result['list'] = $list;
