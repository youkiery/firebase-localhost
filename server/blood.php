<?php

function in() {
  global $db, $data, $result;

  $data->number->{'1'} = $data->number->{'1'} * -1;
  $data->number->{'2'} = $data->number->{'2'} * -1;
  $data->number->{'3'} = $data->number->{'3'} * -1;

  update_blood_sample($data->number);
  $sql = 'update `pet_config` set config_value = ' . $data->total . ' where config_name = "test_blood_number"';
  $db->query($sql);
  $userid = checkUserid();

  $result['status'] = 1;
  $result['total'] = check_last_blood();
  $result['current'] = check_blood_sample();
  return $result;
}

function out() {
  global $db, $data, $result;
  
  $targetid = check_blood_remind('Chạy hóa chất tự động');
  $sample_number = check_last_blood();
  $end = $sample_number - $data->number;
  $userid = checkUserid();

  $sql = 'insert into pet_test_blood_row (time, number, start, end, doctor, target) values(' . time() . ', ' . $data->number . ', ' . $sample_number . ', ' . $end . ', ' . $userid . ', ' . $targetid . ')';
  if ($query = $db->query($sql)) {
    $sql = 'update `pet_config` set config_value = ' . $end . ' where config_name = "test_blood_number"';
    if ($query = $db->query($sql)) {
      $result['status'] = 1;
      $result['number'] = check_last_blood();
    }
  }
  return $result;
}

function import() {
  global $db, $data, $result;
  
  $data->number->{'1'} = $data->number->{'1'} * -1;
  $data->number->{'2'} = $data->number->{'2'} * -1;
  $data->number->{'3'} = $data->number->{'3'} * -1;

  $sql = "insert into pet_test_blood_import(time, price, note, doctor, number1, number2, number3) values(".time().", 0, '', $userid, $data->number->{1}, $data->number->{2}, $data->number->{3})";
  die($sql);
  $db->query($sql);
  update_blood_sample($data->number);
  $userid = checkUserid();

  $result['status'] = 1;
  $result['total'] = check_last_blood();
  $result['number'] = check_blood_number();
  return $result;

}

function insert() {
  global $db, $data, $result;
  
  $targetid = check_blood_remind($data->target);
  $sample_number = check_last_blood();
  $end = $sample_number - $data->number;
  $time = time();
  $userid = checkUserid();

  $sql = 'insert into pet_test_blood_row (time, number, start, end, doctor, target) values(' . $time . ', ' . $data->number . ', ' . $sample_number . ', ' . $end . ', ' . $userid . ', ' . $targetid . ')';
  if ($db->query($sql)) {
    $sql = 'update `pet_config` set config_value = ' . $end . ' where config_name = "test_blood_number"';
    $query = $db->query($sql);

    $result['status'] = 1;
    $result['number'] = check_last_blood();
  }
  return $result;
}

function init() {
  global $db, $data, $result;

  $data->start = time() - 60 * 60 * 24 * 7;
  $data->end = time() + 60 * 60 * 24 - 1;

  $result['start'] = date('d/m/Y', $data->start);
  $result['end'] = date('d/m/Y');
  $result['status'] = 1;
  $result['list'] = getList();
  $result['total'] = check_last_blood();
  $result['current'] = check_blood_sample();
  $result['number'] = check_blood_number();
  return $result;
}

function statistic() {
  
  $from = time() - 60 * 60 * 24 * 30;
  $end = time() + 60 * 60 * 24 - 1;

  $result['status'] = 1;
  $result['statistic'] = status($from, $end);
}

function getList() {
  global $db, $data;

  $sql = 'select * from `pet_test_remind` where name = "blood" order by id';
  $target = $db->obj($sql, 'id', 'value');

  $sql = "select * from ((select id, time, 0 as type from `pet_test_blood_row` where time between $data->start and $data->end) union (select id, time, 1 as type from `pet_test_blood_import` where time between $data->start and $data->end)) as a order by time desc, id desc";
  $record = $db->all($sql);
  $list = array();
  
  foreach ($record as $item) {
    if ($item['type']) $sql = 'select * from `pet_test_blood_import` where id = ' . $item['id'];
    else $sql = 'select * from `pet_test_blood_row` where id = ' . $item['id'];
    $row = $db->fetch($sql);

    $sql = 'select * from `pet_users` where userid = ' . $row['doctor'];
    $user = $db->fetch($sql);

    $list []= array(
      'time' => date('d/m', $row['time']),
      'id' => $item['id'],
      'typeid' => $item['type'],
      'doctor' => (!empty($user['name']) ? $user['name'] : ''),
      'type' => $item['type']
    );
    $len = count($list) - 1;

    if ($item['type']) {
      $list[$len]['target'] = 'N ('. $row['number1'] .'/'. $row['number2'] .'/'. $row['number3'] .') Giá: '. number_format($row['price'], 0, '', ',') .' VND';
      $list[$len]['number'] = '-';
      $list[$len]['number1'] = $row['number1'];
      $list[$len]['number2'] = $row['number2'];
      $list[$len]['number3'] = $row['number3'];
    }
    else {
      $list[$len]['target'] = 'XN: '. (!empty($target[$row['target']]) ? $target[$row['target']] : '');
      $list[$len]['number'] = $row['number'];
    } 
  } 
  return $list;
}

function getCatalogById($id) {
  $sql = 'select * from `pet_test_catalog` where id = ' . $id;
  $query = $db->query($sql);
  return $query->fetch_assoc();
}

function check_last_blood() {
  global $db;
  $sql = 'select * from `pet_config` where config_name = "test_blood_number"';
  if (!empty($row = $db->fetch($sql))) {
    return $row['config_value'];
  }
  $sql = 'insert into `pet_config` (lang, module, config_name, config_value) values ("sys", "site", "test_blood_number", "1")';
  $db->query($sql);
  return 0;
}

function check_blood_sample() {
  global $db;
  $sql = 'select * from `pet_config` where config_name like "test_blood_sample%" order by config_name';
  $query = $db->query($sql);
  $number = array();
  $index = 1;
  while ($row = $query->fetch_assoc()) {
    $number[$index ++] = $row['config_value'];
  }
  return $number;
}

function check_blood_number() {
  global $db;
  $sql = 'select * from `pet_config` where config_name like "test_blood_number%" order by config_name';
  $query = $db->query($sql);
  $number = array();
  $index = 1;
  while ($row = $query->fetch_assoc()) {
    $number[$index ++] = $row['config_value'];
  }
  return $number;
}

function update_blood_sample($data) {
  global $db;
  for ($i = 1; $i <= 3; $i++) {
    $sql = 'update `pet_config` set config_value = config_value + '. $data->{$i} .' where config_name = "test_blood_sample_'. $i .'"';
    $db->query($sql);
  }
}

function check_blood_remind($name) {
  global $db, $data;
  $targetid = 0;
  $sql = 'select * from `pet_test_remind` where name = "blood" and value = "' . $name . '"';
  if (!empty($row = $db->fetch($sql))) {
    $targetid = $row['id'];
  } else {
    $sql = 'insert into `pet_test_remind` (name, value) values ("blood", "' . $name . '")';
    $targetid = $db->insertid($sql);
  }
  return $targetid;
}


function status($from, $end) {
  $total = array(
    'from' => date('d/m/Y', $from),
    'end' => date('d/m/Y', $end),
    'number' => 0,
    'sample' => 0,
    'total' => 0,
    'list' => array()
  );

  $doctor = employ_list();
  $sql = 'select * from `pet_test_blood_row` where (time between ' . $from . ' and ' . $end . ')';
  $query = $db->query($sql);
  $data = array();
  while ($row = $query->fetch_assoc()) {
    if (empty($data[$row['doctor']])) {
      $data[$row['doctor']] = array(
        'name' => $doctor[$row['doctor']],
        'number' => 0,
        'sample' => 0,
      );
    }
    $total['number']++;
    $total['sample'] += $row['number'];
    $total['chemist'] += ($row['start'] - $row['end']);
    $data[$row['doctor']]['number']++;
    $data[$row['doctor']]['sample'] += $row['number'];
  }

  $sql = 'select * from `pet_test_blood_import` where (time between ' . $from . ' and ' . $end . ')';
  $query = $db->query($sql);
  $sum = 0;
  while ($row = $query->fetch_assoc()) {
    $sum += $row['price']; // tổng tiền nhập
  }
  foreach ($data as $row) {
    $total['list'] []= $row;
  }
  $total['total'] = number_format($sum * 1000, 0, '', ',') . ' VNĐ';
  return $total;
}

