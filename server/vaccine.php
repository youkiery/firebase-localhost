<?php
function auto() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['list'] = getlist();
  $result['new'] = getlist(true);
  $result['type'] = gettypeobj();
  $result['doctor'] = getDoctor();
  $result['temp'] = gettemplist();
  
  return $result;
}

function called() {
  global $data, $db, $result;

  $sql = "update pet_test_vaccine set status = 1, note = '". $data->note ."', called = ". time() .", recall = ". (time() + 60 * 60 * 24 * 7) ." where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['list'] = getlist();

  return $result;
}

function confirm() {
  global $data, $db, $result;

  $sql = "update pet_test_vaccine set status = 2 where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['list'] = getlist();
  $result['old'] = getOlder($data->customerid);

  return $result;
}

function dead() {
  global $data, $db, $result;

  $sql = "update pet_test_vaccine set status = 3, note = '$data->note' where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['list'] = getlist();
  
  return $result;
}

function done() {
  global $data, $db, $result;

  $sql = "update pet_test_vaccine set status = 2 where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['list'] = getlist();
  $result['old'] = getOlder($data->customerid);

  return $result;
}

function donerecall() {
  global $data, $db, $result;

  $sql = "update pet_test_vaccine set status = 2 where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['list'] = getlist();
  return $result;
}

function excel() {
  global $data, $db, $result;

  $page_title = "Nhập hồ sơ một cửa";
  $x = array(
    'A' => 0, 'B' => 1, 'C' => 2, 'D' => 3, 'E' => 4, 'F' => 5, 'G' => 6, 'H' => 7, 'I' => 8, 'J' => 9, 'K' => 10, 'L' => 11, 'M' => 12, 'N' => 13, 'O' => 14, 'P' => 15, 'Q' => 16, 'R' => 17, 'S' => 18, 'T' => 19, 'U' => 20, 'V' => 21, 'W' => 22, 'X' => 23, 'Y' => 24, 'Z' => 25
  );
  $xr = array(0 => 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');

  $dir = str_replace('/server/', '/', ROOTDIR);

  include $dir .'PHPExcel/IOFactory.php';
  $inputFileName = $dir .'upload/ChiTietHoaDon_HD250167_KV24092021-103818-243.xlsx';
    
  $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
  $objReader = PHPExcel_IOFactory::createReader($inputFileType);
  $objPHPExcel = $objReader->load($inputFileName);

  $sheet = $objPHPExcel->getSheet(0); 

  $highestRow = $sheet->getHighestRow(); 
  $highestColumn = $sheet->getHighestColumn();

  $sql = "select * from pet_test_doctor";
  $doctor = obj($sql, 'name', 'userid');

  $sql = "select * from pet_test_type";
  $type = obj($sql, 'code', 'id');

  $col = array(
    'Mã hàng' => '', // 0
    'Tên người bán' => '', // 1
    'Số điện thoại' => '', // 2
    'Khách hàng' => '', // 3
    'Ngày bán' => '', // 4
    'Ghi chú' => '' // 5
  );

  for ($j = 0; $j <= $x[$highestColumn]; $j ++) {
    $val = $sheet->getCell($xr[$j] . '1')->getValue();
    foreach ($col as $key => $value) {
      if ($key == $val) $col[$key] = $j;
    }
  }

  $data = array();
  for ($i = 2; $i <= $highestRow; $i ++) { 
    $temp = array();
    foreach ($col as $key => $j) {
      $val = $sheet->getCell($xr[$j] . $i)->getValue();
      $temp []= $val;
    }
    $data []= $temp;
  }

  foreach ($data as $row) {
    $sql = "select * from pet_test_customer where phone = '$row[2]'";
    if (empty($c = $db->fetch($sql))) {
      $sql = "insert into pet_test_customer (name, phone) values('$row[3]', '$row[2]')";
      $c['id'] = $db->insertid($sql);
    }

    $datetime = explode(' ', $row[4]);
    $date = explode('-', $datetime[0]);
    // echo json_encode($date);
    // die();
    $cometime = strtotime("$date[0]/$date[1]/$date[2]");

    $datetime = explode(' ', $row[5]);
    $date = explode('-', $datetime[0]);
    if (count($date) == 3) $calltime = strtotime("$date[0]/$date[1]/$date[2]");
    else $calltime = time();

    echo "insert into pet_test_vaccine (customerid, typeid, cometime, calltime, note, status, recall, userid, time, called) values($c[id], ". $type[$row[0]] .", $cometime, $calltime, '', 5, $calltime, ". $doctor[$row[1]] .", ". time() .", 0) <br>";
  }
  return $result;
}


function filter() {
  global $data, $db, $result;
  $keyword = parseGetData('keyword', '');

  $data = array();
  $type = gettypeobjList();
  
  $sql = 'select b.name as petname, c.phone, a.typeid, a.calltime, a.note, a.note, a.status from pet_test_vaccine a inner join pet_test_pet b on a.petid = b.id inner join pet_test_customer c on b.customerid = c.id where c.name like "%'. $keyword .'%" or c.phone like "%'. $keyword .'%" order by a.calltime';
  $query = $mysqli->$db->query($sql);
  
  // tên thú cưng, sđt, vaccine, ngày tái chủng, ghi chú, trạng thại
  while ($row = $db->fetch_assoc()) {
    $data []= array(
      'petname' => $row['petname'],
      'number' => $row['phone'],
      'vaccine' => $type[$row['typeid']],
      'calltime' => $row['calltime'],
      'note' => $row['note'],
      'status' => $row['status'],
    );
  }
  
  $result['status'] = 1;
  $result['data'] = $data;
  return $result;
}

function insert() {
  global $data, $db, $result;

  $sql = "select * from pet_test_customer where phone = '$data->phone'";
  if (!empty($customer = $db->fetch($sql))) {
    $sql = "update pet_test_customer set name = '$data->name' where id = $customer[id]";
    $db->query($sql);
  }
  else {
    $sql = "insert into pet_test_customer (name, phone, address) values ('$data->name', '$data->phone', '')";
    $customer['id'] = $db->insertid($sql);
  }
  
  $data->cometime = totime($data->cometime);
  $data->calltime = totime($data->calltime);
  $userid = checkUserid();
  
  $sql = "insert into pet_test_vaccine (customerid, typeid, cometime, calltime, note, status, called, recall, userid, time) values ($customer[id], $data->typeid, $data->cometime, $data->calltime, '', 0, 0, $data->calltime, $userid, ". time() .")";
  $db->query($sql);
  $result['status'] = 1;
  $result['new'] = getlist(true);
  $result['old'] = getOlder($customer['id']);

  return $result;
}

function insertdoctor() {
  global $data, $db, $result;
  $sql = "insert into pet_test_doctor (userid, name) values('$data->user', '$data->name')";
  $db->query($sql);
  $result['status'] = 1;
  $result['list'] = getDoctor();

  return $result;
}

function inserttype() {
  global $data, $db, $result;
  $sql = "insert into pet_test_type (name, code) values('$data->name', '$data->code')";
  $db->query($sql);
  $result['status'] = 1;
  $result['list'] = gettypeobj();
  return $result;
}

function removedoctor() {
  global $data, $db, $result;
  $sql = "delete from pet_test_doctor where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['list'] = getDoctor();
  
  return $result;
}

function removetype() {
  global $data, $db, $result;
  $sql = "update pet_test_type set active = 0 where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['list'] = typeList();
  return $result;
}

function search() {
  global $data, $db, $result;
  $result['status'] = 1;
  $result['list'] = getlist();

  return $result;
}

function searchdoctor() {
  global $data, $db, $result;
  $sql = "select userid, username, concat(last_name, ' ', first_name) as name from pet_users where (last_name like '%$data->keyword%' or first_name like '%$data->keyword%' or username like '%$data->keyword%') and userid not in (select userid from pet_test_doctor)";
  $result['status'] = 1;
  $result['list'] = $db->all($sql);
  return $result;
}

function update() {
  global $data, $db, $result;

  $data->cometime = totime($data->cometime);
  $data->calltime = totime($data->calltime);
  
  $sql = "update pet_test_vaccine set typeid = $data->typeid, cometime = $data->cometime, calltime = $data->calltime where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  if (!empty($data->prv)) {
    $result['list'] = gettemplist();
  }
  else {
    $result['list'] = getlist();
    $result['new'] = getlist(true);
  }

  return $result;
}

function uncalled() {
  global $data, $db, $result;

  $sql = "update pet_test_vaccine set status = 1, note = '". $data->note ."', called = ". time() .", recall = ". (time() + 60 * 60 * 24) ." where id = $data->id";
  // die($sql);
  $db->query($sql);
  $result['status'] = 1;
  $result['list'] = getlist();
  
  return $result;
}

function updatedoctor() {
  global $data, $db, $result;

  $sql = "update pet_test_doctor set userid = $data->user, name = '$data->name' where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['list'] = getDoctor();
  return $result;
}

function updatetype() {
  global $data, $db, $result;

  $sql = "update pet_test_type set name = '$data->name', code = '$data->code' where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['list'] = gettypeobj();
  
  return $result;
}

function getlist($today = false) {
  global $db, $data, $userid;

  $userid = checkUserid();
  $sql = "select * from pet_test_user_per where userid = $userid and module = 'vaccine'";
  $role = $db->fetch($sql);

  $xtra = '';
  if ($role['type'] == 1) $xtra = " and userid = $userid ";

  $type = typeList();
  $start = strtotime(date('Y/m/d'));
  if ($today) {
    $sql = "select a.*, c.first_name as doctor, b.name, b.phone, b.address from pet_test_vaccine a inner join pet_users c on a.userid = c.userid inner join pet_test_customer b on a.customerid = b.id where (a.time between $start and ". time() . ") $xtra and a.status < 2 order by a.id desc limit 50";
  }
  else if (empty($data->keyword)) {
    $end = $start + 60 * 60 * 24 - 1; 
    // x
    $sql = "select a.*, c.first_name as doctor, b.name, b.phone, b.address from pet_test_vaccine a inner join pet_users c on a.userid = c.userid inner join pet_test_customer b on a.customerid = b.id where a.recall < $end $xtra and a.status < 2 order by a.calltime asc, a.recall desc limit 50";
  }
  else {
    $key = $data->keyword;
    $sql = "select a.*, c.first_name as doctor, b.name, b.phone, b.address from pet_test_vaccine a inner join pet_users c on a.userid = c.userid inner join pet_test_customer b on a.customerid = b.id where (b.name like '%$key%' or b.phone like '%$key%') order by a.calltime asc, a.recall desc limit 50";
  }

  $v = $db->all($sql);
  $list = array();

  // luật tính status
  // nếu chưa gọi, chưa cách quá 7 ngày, status = 0
  // nếu đã gọi, chưa cách quá 7 ngày status = 1
  // nếu đã gọi, cách quá 7 ngày status = 2
  // nếu chưa gọi, cách quá 7 ngày, status = 3

  $limit = $start - 60 * 60 * 24 * 7;
  foreach ($v as $row) {
    $status = $row['status'];
    if ($status) {
      if ($row['calltime'] < $limit) $status = 2;
      else $status = 1;
    }
    else {
      if ($row['calltime'] < $limit) $status = 3;
      else $status = 0;
    }

    $list []= array(
      'id' => $row['id'],
      'note' => $row['note'],
      'doctor' => $row['doctor'],
      'name' => $row['name'],
      'phone' => $row['phone'],
      'address' => $row['address'],
      'status' => $status,
      'vaccine' => $type[$row['typeid']],
      'called' => ($row['called'] ? date('d/m/Y', $row['called']) : '-'),
      'cometime' => date('d/m/Y', $row['cometime']),
      'calltime' => date('d/m/Y', $row['calltime']),
    );
  }

  return $list;
}

function gettemplist($today = false) {
  global $db, $data;
  $userid = checkUserid();

  $sql = "select * from pet_test_user_per where userid = $userid and module = 'vaccine'";
  $role = $db->fetch($sql);

  $xtra = '';
  if ($role['type'] == 1) $xtra = " and userid = $userid ";

  $sql = "select a.*, c.first_name as doctor, b.name, b.phone, b.address from pet_test_vaccine a inner join pet_users c on a.userid = c.userid inner join pet_test_customer b on a.customerid = b.id where a.status = 5 $xtra order by a.id desc limit 50";
  $v = $db->all($sql);
  $list = array();

  $type = typeList();
  foreach ($v as $row) {
    $list []= array(
      'id' => $row['id'],
      'note' => $row['note'],
      'doctor' => $row['doctor'],
      'name' => $row['name'],
      'phone' => $row['phone'],
      'address' => $row['address'],
      'vaccine' => $type[$row['typeid']],
      'called' => ($row['called'] ? date('d/m/Y', $row['called']) : ''),
      'cometime' => date('d/m/Y', $row['cometime']),
      'calltime' => date('d/m/Y', $row['calltime']),
    );
  }

  return $list;
}

function getOlder($customerid) {
  global $db;

  $sql = "select * from pet_test_vaccine where status < 2 and customerid = $customerid order by id asc";
  $list = $db->all($sql);
  $query = $db->query($sql);
  $type = typeList();
  $start = strtotime(date('Y/m/d'));
  $limit = $start - 60 * 60 * 24 * 7;

  foreach ($list as $index => $row) {
    $status = $row['status'];
    if ($status == 2) $status = 4;
    else if ($status == 1) {
      if ($row['calltime'] < $limit) $status = 2;
      else $status = 1;
    }
    else {
      if ($row['calltime'] < $limit) $status = 3;
      else $status = 0;
    }

    $list[$index]['type'] = $type[$row['typeid']];
    $list[$index]['cometime'] = date('d/m/Y', $row['cometime']);
    $list[$index]['calltime'] = date('d/m/Y', $row['calltime']);
    $list[$index]['status'] = $status;
    $list[$index]['called'] = ($row['called'] ? date('d/m/Y', $row['called']) : '-');
  }

  return $list;
}

function typeList() {
  global $db;
  $sql = 'select * from pet_test_type where active = 1';
  return $db->obj($sql, 'id', 'name');
}

function gettypeobj() {
  global $db;

  $sql = 'select * from pet_test_type where active = 1';
  return $db->all($sql);
}

function getDoctor() {
  global $db;

  $sql = "select a.id, a.userid, a.name, b.username from pet_test_doctor a inner join pet_users b on a.userid = b.userid";
  return $db->all($sql);
}
