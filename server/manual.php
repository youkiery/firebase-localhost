<?php
function init() {
  global $data, $db, $result;
  
  $result['status'] = 1;
  $result['list'] = getList();
  return $result;
}

function getList() {
  global $db, $data;

  $sql = 'select * from pet_test_manual order by module asc, id asc';
  $list = $db->all($sql);

  foreach ($list as $key => $row) {
    $sql = "select * from pet_test_manual_row where manualid = $row[id] order by id asc";
    $list[$key]['list'] = $db->all($sql);
  }
  return $list;
}

function remove() {
  global $data, $db, $result;

  $sql = "delete from pet_test_manual where id = $data->id";
  $db->query($sql);

  $sql = "delete from pet_test_manual_row where manualid = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = getList();
  return $result;
}

function save() {
  global $data, $db, $result;

  if ($data->id) {
    $sql = "update pet_test_manual set title = '$data->title', module = '$data->module' where id = $data->id";
    $id = $db->query($sql);

    $sql = "delete from pet_test_manual_row where manualid = $data->id";
    $db->query($sql);

    foreach ($data->list as $key => $row) {
      $sql = "insert into pet_test_manual_row (content, type, manualid) values('$row->content', '$row->type', $id)";
      $db->query($sql);
    }
  }
  else {
    $sql = "insert into pet_test_manual (title, module) values('$data->title', '$data->module')";
    $id = $db->insertid($sql);
  
    foreach ($data->list as $key => $row) {
      $sql = "insert into pet_test_manual_row (content, type, manualid) values('$row->content', '$row->type', $id)";
      $db->query($sql);
    }
  }

  $result['status'] = 1;
  $result['list'] = getList();
  return $result;
}

