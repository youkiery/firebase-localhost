<?php

$filter = array(
  'keyword' => parseGetData('keyword', ''),
  'page' => parseGetData('page', 0),
);
$id = parseGetData('id', '')

$sql = 'delete from pet_test_target where id = '. $id;
$query = $mysqli->query($sql);


$sql = 'select id, name, customer, time from pet_test_profile where name like "%'. $filter['keyword'] .'%" or customer like "%'. $filter['keyword'] .'%" order by id desc limit 10 offset '. ($filter['page'] - 1) * 10;
$query = $mysqli->query($sql);
$list = array();

while ($row = $query->fetch_assoc()) {
  $list []= $row;
}

$result['status'] = 1;
$result['list'] = $list;
