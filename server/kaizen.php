<?php

function remove() {
  global $db, $data, $result;
  
  $result['status'] = 1;
  removeData();
  $result['messenger'] = 'Đã xóa giải pháp';
  $result['list'] = initList();
  
  return $result;
}

function insert() {
  global $db, $data, $result;
  
  insertData();
  
  $result['status'] = 1;
  $result['list'] = initList();
  $result['messenger'] = 'Đã thêm giải pháp';

  return $result;
}

function init() {
  global $db, $data, $result;

  $result['status'] = 1;
  $result['list'] = initList();
   
  return $result;
}

function edit() {
  global $db, $data, $result;

  $result['status'] = 1;
  updateData();
  $result['list'] = initList();

  return $result;
}

function done() {
  global $db, $data, $result;

  checkData();
  $result['status'] = 1;
  $result['messenger'] = 'Đã hoàn thành';
  $result['list'] = initList();

  return $result;
}

function initList() {
  global $db, $data;

  $filter = $data->filter;
  $filter->endtime = isodatetotime($filter->endtime) + 60 * 60 * 24 - 1;
  $filter->starttime = isodatetotime($filter->starttime);

  $list = array(
    'done' => array(),
    'undone' => array(),
  );
  $xtra = array();
  $userid = checkUserid();
  $role = checkRole();

  if (!empty($filter->keyword)) $xtra []= '((result like "%'. $filter->keyword .'%") or (solution like "%'. $filter->keyword .'%") or (problem like "%'. $filter->keyword .'%"))';
  if ($role < 2) $xtra []= 'userid = ' . $userid;
  if (count($xtra)) $xtra = ' and ' . implode(' and ', $xtra);
  else $xtra = '';
  
  $sql = 'select * from pet_test_kaizen where active = 1 ' . $xtra . ' and done = 0 order by edit_time desc limit '. $filter->undone * 10;
  $query = $db->query($sql);
  
  while ($row = $query->fetch_assoc()) {
    $user = checkUserById($row['userid']);
    $data = array(
      'id' => $row['id'],
      'name' => $user['name'],
      'done' => intval($row['done']),
      'problem' => $row['problem'],
      'solution' => $row['solution'],
      'result' => $row['result'],
      'time' => date('d/m/Y', $row['edit_time'])
    );
    $list['undone'] []= $data;
  }
  
  $sql = 'select * from pet_test_kaizen where active = 1 ' . $xtra . ' and done = 1 and (edit_time between '. $filter->starttime .' and '. $filter->endtime .') order by edit_time desc limit '. $filter->done * 10;
  $query = $db->query($sql);
  
  while ($row = $query->fetch_assoc()) {
    $user = checkUserById($row['userid']);
    $data = array(
      'id' => $row['id'],
      'name' => $user['name'],
      'done' => intval($row['done']),
      'problem' => $row['problem'],
      'solution' => $row['solution'],
      'result' => $row['result'],
      'time' => date('d/m/Y', $row['edit_time'])
    );
    $list['done'] []= $data;
  }
  return $list;    
}

function getKaizenById($id) {
  global $db;
  $sql = 'select * from pet_test_kaizen where id = ' . $id;
  $query = $db->query($sql);
  return $query->fetch_assoc();
}

function insertData() {
  global $db, $data;

  $userid = checkUserid();
  $dat = $data->data;

  $sql = 'insert into pet_test_kaizen (userid, problem, solution, result, post_time, edit_time) values('. $userid .', "'. $dat->problem .'", "'. $dat->solution .'", "'. $dat->result .'", '. time() .', '. time() .')';
  $db->query($sql);
}

function updateData() {
  global $db, $data;

  $data = $data->data;

  $sql = 'update pet_test_kaizen set problem = "'. $data->problem .'", solution = "'. $data->solution .'", result = "'. $data->result .'", edit_time = '. time() .' where id = '. $data->id;
  $db->query($sql);
}

function removeData() {
  global $db, $data;
  $sql = 'update pet_test_kaizen set active = 0 where id = '. $data->id;
  $db->query($sql);
}

function checkData() {
  global $db, $data;
  if ($data->type == 'done') $type = 1;
  else $type = 0;
  $sql = 'update pet_test_kaizen set done = '. $type .' where id = '. $data->id;
  $db->query($sql);
  return $time;
}

function checkRole() {
  global $db;

  $userid = checkUserid();
  $sql = "select * from pet_test_user_per where module = 'kaizen' and userid = $userid";
  if (!empty($p = $db->fetch($sql))) return $p['type'];
  return 0;
}
