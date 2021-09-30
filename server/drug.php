<?php

function auto() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['list'] = getlist();
  return $result;
}

function insert() {
  global $data, $db, $spa_option;

  $sql = "select * from pet_test_heal_medicine where name = '$data->name' limit 1";
  if (empty($db->fetch($sql))) {
    $sql = "insert into pet_test_heal_medicine (code, name, unit, system, limits, effect, effective, disease, note, sideeffect, mechanic, image) values('', '$data->name', '', '', '$data->limits', '$data->effect', '', '', '', '$data->sideeffect', '$data->mechanic', '". implode(', ', $data->image) ."')";
    $db->query($sql);
    $result['status'] = 1;
    $data = $data->filter;
    $result['list'] = getList();
  }
  else {
    $result['messenger'] = 'Tên thuốc đã tồn tại';
  }
  return $result;
}

function update() {
  global $data, $db, $spa_option;

  $sql = "select * from pet_test_heal_medicine where name = '$data->name' and id <> $data->id limit 1";
  if (empty($db->fetch($sql))) {
    $sql = "update pet_test_heal_medicine set name = '$data->name', limits = '$data->limits', effect = '$data->effect', sideeffect = '$data->sideeffect', mechanic = '$data->mechanic', image = '".implode(', ', $data->image)."' where id = $data->id";
    $db->query($sql);
    $result['status'] = 1;
    $data = $data->filter;
    $result['list'] = getList();
  }
  else {
    $result['messenger'] = 'Tên thuốc đã tồn tại';
  }
  return $result;
}

function remove() {
  global $data, $db, $spa_option;

  $sql = 'delete from pet_test_heal_medicine where id = '. $data->id;
  $query = $db->query($sql);
  $result['status'] = 1;
  $result['list'] = getList();
  return $result;
}

function getList() {
  global $data, $db, $spa_option;

  $xtra = array();
  if (!empty($data->name)) $xtra []= 'name like "%'. $data->name .'%"';
  if (!empty($data->effect)) $xtra []= 'effect like "%'. $data->effect .'%"';
  if (count($xtra)) $xtra = 'where '. implode(' and ', $xtra);
  else $xtra = '';

  $sql = 'select * from pet_test_heal_medicine '. $xtra .' order by name limit 30';
  $query = $db->query($sql);
  $list = array();

  while ($row = $query->fetch_assoc()) {
    $image = explode(', ', $row['image']);
    $row['limits'] = urldecode($row['limits']);
    $row['effect'] = urldecode($row['effect']);
    $row['sideeffect'] = urldecode($row['sideeffect']);
    $row['mechanic'] = urldecode($row['mechanic']);
    $row['image'] = (count($image) && !empty($image[0]) ? $image : array());
    $list []= $row;
  }
  return $list;
}
