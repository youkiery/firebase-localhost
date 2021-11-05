<?php
function init() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['list'] = getlist();
    
  return $result;
}

function insert() {
  global $data, $db, $result;

  $sql = "select * from pet_test_target where name = '$data->name' and module = '$data->module'";
  if (empty($row = $db->fetch($sql))) {
    $sql = "insert into pet_test_target (name, number, active, unit, intro, flag, up, down, disease, aim, module) values('$data->name', 0, 1, '$data->unit', '$data->intro', '$data->flag', '$data->up', '$data->down', '$data->disease', '$data->aim', '$data->module')";
    $query = $db->query($sql);
  }
  else {
    // $sql = "update pet_test_target set name = '$data->name', active = 1, unit = '$data->unit', intro = '$data->intro', flag = '$data->flag', up = '$data->up', down = '$data->down', disease = '$data->disease', aim = '$data->aim' where id = $row->id";
    $result['messenger'] = 'Chỉ tiêu đã tồn tại';
  }

  $result['status'] = 1;
  $result['list'] = getlist();
      
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

  $sql = 'update pet_test_target set number = 0 where id = '. $data->id;
  $db->query($sql);
  $result['status'] = 1;

  return $result;
}

function search() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['list'] = getlist();
        
  return $result;
}

function updateinfo() {
  global $data, $db, $result;
      
  $sql = "update pet_test_target set name = '$data->name', intro = '$data->intro', unit = '$data->unit', flag = '$data->flag', up = '$data->up', down = '$data->down', disease = '$data->disease', aim = '$data->aim' where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = getlist();

  return $result;
}

function update() {
  global $data, $db, $result;

  $sql = "update pet_test_target set number = number + 1 where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;

  return $result;
}

function getlist() {
  global $db, $data;
  $sql = "select * from pet_test_target where active = 1 and module = '$data->module' and name like '%$data->key%' order by id asc ";
  return $db->all($sql);
}