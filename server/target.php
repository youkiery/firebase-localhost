<?php
function init() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['list'] = getlist();
    
  return $result;
}

function insert() {
  global $data, $db, $result;

  $sql = "select * from pet_test_target where name = $data->name";
  $query = $db->query($sql);
  if (empty($row = $query->fetch_assoc())) {
    $sql = "insert into pet_test_target (name, number, active, unit, intro, flag, up, down, disease, aim) values('$data[name]', 0, 1, '$data[unit]', '$data[intro]', '$data[flag]', '$data[up]', '$data[down]', '$data[disease]', '$data[aim]')";
  }
  else {
    $sql = "update pet_test_target set name = '$data[name]', active = 1, unit = '$data[unit]', intro = '$data[intro]', flag = '$data[flag]', up = '$data[up]', down = '$data[down]', disease = '$data[disease]', aim = '$data[aim]' where id = $row[id]";
  }
  $query = $db->query($sql);

  $result['status'] = 1;
  $result['list'] = $target->init($key);
      
  return $result;
}

function remove() {
  global $data, $db, $result;
      
  $sql = 'update pet_test_target set active = 0 where id = '. $data->id;
  $query = $db->query($sql);
  $result['status'] = 1;
  $result['list'] = getlist();

  return $result;
}

function res() {
  global $data, $db, $result;

  $sql = 'update pet_test_target set number = 0 where id = '. $id;
  $db->query($sql);
  $result['status'] = 1;

  return $result;
}

function search() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['list'] = $target->init($key);
        
  return $result;
}

function updateinfo() {
  global $data, $db, $result;
      
  $sql = "update pet_test_target set name = '$data[name]', intro = '$data[intro]', unit = '$data[unit]', flag = '$data[flag]', up = '$data[up]', down = '$data[down]', disease = '$data[disease]', aim = '$data[aim]' where id = ". $data['id'];
  $mysqli->query($sql);

  $result['status'] = 1;
  $result['list'] = $target->init($key);

  return $result;
}

function update() {
  global $data, $db, $result;

  $sql = 'update pet_test_target set number = number + 1 where id = '. $id;
  $db->query($sql);

  $result['status'] = 1;
  $target->update($id);

  return $result;
}

function getlist() {
  global $db;
  $sql = 'select * from pet_test_target where active = 1 and name like "%'. $key .'%" order by id asc ';
  $query = $db->query($sql);
  $list = array();

  while ($row = $query->fetch_assoc()) {
    $list []= $row;
  }
  return $list;
}