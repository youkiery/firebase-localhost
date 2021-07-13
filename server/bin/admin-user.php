<?php

$sql = 'select username, concat(a.last_name, " ", a.first_name) as fullname, a.userid from pet_users a inner join pet_test_user b on a.userid = b.userid';
$query = $mysqli->query($sql);
$list = array();

$module = array(
  'work' => 0,
  'kaizen' => 0,
  'schedule' => 0,
  'vaccine' => 0,
  'spa' => 0,
  'expire' => 0,
  'blood' => 0,
  'usg' => 0,
  'drug' => 0,
  'profile' => 0,
);

while ($row = $query->fetch_assoc()) {
  $sql = 'select * from pet_test_permission where userid = '. $row['userid'];
  $permist_query = $mysqli->query($sql);
  $permists = $module;

  while ($permist = $permist_query->fetch_assoc()) {
    $permists[$permist['module']] = $permist['type'];
  }
  $row['module'] = $permists;

  $list []= $row;
}

$result['status'] = 1;
$result['users'] = $list;

