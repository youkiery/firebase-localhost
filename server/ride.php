<?php
function init() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['clock'] = getclock();
  $result['list'] = getlist();
  return $result;
}

function remove() {
  global $data, $db, $result;

  if ($data->segment == '0') $sql = "delete from pet_test_ride where id = $data->id";
  else $sql = "delete from pet_test_import where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = getlist();
  return $result;
}

function cole() {
  global $data, $db, $result;

  $userid = checkUserid();
  $money = str_replace(',', '', $data->money);
  $time = time();
  $sql = "insert into pet_test_ride (userid, clockf, clocke, money, destination, note, time) values($userid, $data->clockf, $data->clocke, $money, '$data->destination', '$data->note', $time)";
  $db->query($sql);

  $sql = "update pet_test_config set name = '$data->clocke' where module = 'clock'";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = getlist();
  return $result;
}

function pay() {
  global $data, $db, $result;

  $userid = checkUserid();
  $money = str_replace(',', '', $data->money);
  $time = time();
  $sql = "insert into pet_test_import (userid, price, module, note, time) values($userid, $money, 'ride', '$data->note', $time)";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = getlist();
  return $result;
}

function getclock() {
  global $db, $data;

  $sql = "select * from pet_test_config where module = 'clock'";
  if (empty($c = $db->fetch($sql))) {
    $sql = "insert into pet_test_config (module, name, value) values ('clock', '0','')";
    $db->query($sql);
    return 0;
  }
  return $c['name'];
}

function getlist($today = false) {
  global $db, $data;

  $data->start = isodatetotime($data->start);
  $data->end = isodatetotime($data->end);
  $ride = "select a.*, b.name as user from pet_test_ride a inner join pet_users b on a.userid = b.userid where (a.time between $data->start and $data->end)";
  $pay = "select a.*, b.name as user from pet_test_import a inner join pet_users b on a.userid = b.userid where a.module = 'ride' and (a.time between $data->start and $data->end)";

  return array(
    0 => $db->all($ride),
    $db->all($pay),
  );
}

function statistic() {
  global $data, $db, $result;

  $data->start = isodatetotime($data->start);
  $data->end = isodatetotime($data->end) + 60 * 60 * 24 - 1;

  $userid = checkuserid();
  $sql = "select * from pet_test_user_per where userid = $userid and module = 'ride' and type = 2";
  $xtra = "";
  if (empty($p = $db->fetch($sql))) $xtra = "a.userid = $userid and";

  $sql = "select * from pet_test_ride where $xtra (time between $data->start and $data->end) order by id desc";
  $cole = $db->all($sql);
  $sql = "select * from pet_test_import where $xtra module = 'ride' and (time between $data->start and $data->end) order by id desc";
  $pay = $db->all($sql);
  $data = array(
    'cole' => 0, 'pay' => 0, 'count' => 0, 'list' => array()
  );
  $temp = array();

  foreach ($cole as $row) {
    $data['cole'] += $row['money'];
    $data['count'] ++;
    if (empty($temp[$row['userid']])) {
      $sql = "select name from pet_users where userid = $row[userid]";
      $u = $db->fetch($sql);
      $temp[$row['userid']] = array('name' => $u['name'], 'clock' => 0, 'cole' => 0, 'pay' => 0, 'count' => 0);
    }
    $temp[$row['userid']]['clock'] += $row['clocke'] - $row['clockf'];
    $temp[$row['userid']]['cole'] += $row['money'];
    $temp[$row['userid']]['count'] ++;
  }

  foreach ($pay as $row) {
    $data['pay'] += $row['price'];
    if (empty($temp[$row['userid']])) {
      $sql = "select name from pet_users where userid = $row[userid]";
      $u = $db->fetch($sql);
      $temp[$row['userid']] = array('name' => $u['name'], 'clock' => 0, 'cole' => 0, 'pay' => 0, 'count' => 0);
    }
    $temp[$row['userid']]['pay'] = $row['price'];
  }

  foreach ($temp as $row) {
    $data['list'][] = $row;
  }
  
  $result['status'] = 1;
  $result['data'] = $data;
  return $result;
}
