<?php
function auto() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['list'] = getlist();
  $result['new'] = getlist(true);
  $result['type'] = gettypeobj();
  $result['doctor'] = getDoctor();
  $result['temp'] = gettemplist();
  $result['over'] = getOverlist();
  
  return $result;
}

function called() {
  global $data, $db, $result;

  $sql = "select * from pet_test_vaccine where id = $data->id";
  $v = $db->fetch($sql);
  $time = time();
  $recall = $time + 60 * 60 * 24 * 3;

  $sql = "update pet_test_vaccine set status = 2, note = '". $data->note ."', called = $time, recall = $recall where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['list'] = getlist();

  return $result;
}

function uncalled() {
  global $data, $db, $result;

  $sql = "select * from pet_test_vaccine where id = $data->id";
  $v = $db->fetch($sql);
  $time = time();
  $recall = $time + 60 * 60 * 24 * 3;

  $sql = "update pet_test_vaccine set status = 1, note = '". $data->note ."', called = $time, recall = $recall where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['list'] = getlist();
  
  return $result;
}

function confirm() {
  global $data, $db, $result;

  $sql = "select * from pet_test_vaccine where id = $data->id";
  $c = $db->fetch($sql);

  $sql = "update pet_test_vaccine set status = 0, recall = calltime where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['list'] = getlist();
  $result['temp'] = gettemplist();
  $result['old'] = getOlder($c['customerid']);

  return $result;
}

function dead() {
  global $data, $db, $result;

  $sql = "update pet_test_vaccine set status = 4, note = '$data->note' where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['list'] = getlist();
  
  return $result;
}

function done() {
  global $data, $db, $result;

  $sql = "update pet_test_vaccine set status = 3 where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['list'] = getlist();
  $result['old'] = getOlder($data->customerid, $data->vid);

  return $result;
}

function donerecall() {
  global $data, $db, $result;

  $sql = "update pet_test_vaccine set status = 3 where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['list'] = getlist();
  return $result;
}

function excel() {
  global $data, $db, $result, $_FILES;

  $dir = str_replace('/server', '/', ROOTDIR);

  $raw = $_FILES['file']['tmp_name'];
  $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
  $name = pathinfo($_FILES['file']['name'], PATHINFO_FILENAME);
  $file_name = $name . "-". time() . ".". $ext;
  $des = $dir ."export/$file_name";

  move_uploaded_file($raw, $des);

  $x = array();
  $xr = array(0 => 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ', 'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'HI', 'BJ');
  foreach ($xr as $key => $value) {
    $x[$value] = $key;
  }

  include $dir .'PHPExcel/IOFactory.php';
    
  $inputFileType = PHPExcel_IOFactory::identify($des);
  $objReader = PHPExcel_IOFactory::createReader($inputFileType);
  $objPHPExcel = $objReader->load($des);
  
  $sheet = $objPHPExcel->getSheet(0); 

  $highestRow = $sheet->getHighestRow(); 
  $highestColumn = $sheet->getHighestColumn();

  $sql = "select * from pet_test_doctor";
  $doctor = $db->obj($sql, 'name', 'userid');

  $sql = "select * from pet_test_type";
  $type = $db->obj($sql, 'code', 'id');

  $col = array(
    'Mã hàng' => '', // 0
    'Người bán' => '', // 1
    'Điện thoại' => '', // 2
    'Tên khách hàng' => '', // 3
    'Thời gian' => '', // 4 01/10/2021 18:58:47
    'Ghi chú' => '' // 5
  );

  for ($j = 0; $j <= $x[$highestColumn]; $j ++) {
    $val = $sheet->getCell($xr[$j] . '1')->getValue();
    foreach ($col as $key => $value) {
      if ($key == $val) $col[$key] = $j;
    }
  }

  $exdata = array();
  for ($i = 2; $i <= $highestRow; $i ++) { 
    $temp = array();
    foreach ($col as $key => $j) {
      $val = $sheet->getCell($xr[$j] . $i)->getValue();
      $temp []= $val;
    }
    $exdata []= $temp;
  }

  $l = array();
  foreach ($exdata as $row) {
    if (isset($type[$row[0]])) {

      $sql = "select * from pet_test_customer where phone = '$row[2]'";
      if (empty($c = $db->fetch($sql))) {
        $c['id'] = 0;
      }
  
      $datetime = explode(' ', $row[4]);
      $date = explode('/', $datetime[0]);
      $cometime = strtotime("$date[2]/$date[1]/$date[0]");
  
      $date = explode('/', $row[5]);
      if (count($date) == 3) $calltime = strtotime("$date[2]/$date[1]/$date[0]");
      else $calltime = 0;

      // $row['vaccine'] = getvacid($type[$row[0]])['name'];
      // $row['cometime'] = date('d/m/Y', $cometime);
      // $row['calltime'] = date('d/m/Y', $calltime);
      // $l []= $row;
  
      $sql = "insert into pet_test_vaccine (customerid, typeid, cometime, calltime, note, status, recall, userid, time, called) values($c[id], ". $type[$row[0]] .", $cometime, $calltime, '', 5, $calltime, ". $doctor[$row[1]] .", ". time() .", 0)";
      $db->query($sql);
    }
  }
  $result['list'] = gettemplist();
  return $result;
}

function getvacid($id) {
  global $db;
  $sql = "select * from pet_test_type where id = $id";
  return $db->fetch($sql);
}

function filter() {
  global $data, $db, $result;

  $exdata = array();
  $type = gettypeobjList();
  
  $sql = 'select b.name as petname, c.phone, a.typeid, a.calltime, a.note, a.note, a.status from pet_test_vaccine a inner join pet_test_pet b on a.petid = b.id inner join pet_test_customer c on b.customerid = c.id where c.name like "%'. $data->keyword .'%" or c.phone like "%'. $data->keyword .'%" order by a.calltime';
  $query = $db->query($sql);
  
  // tên thú cưng, sđt, vaccine, ngày tái chủng, ghi chú, trạng thại
  while ($row = $db->fetch_assoc()) {
    $exdata []= array(
      'petname' => $row['petname'],
      'number' => $row['phone'],
      'vaccine' => $type[$row['typeid']],
      'calltime' => $row['calltime'],
      'note' => $row['note'],
      'status' => $row['status'],
    );
  }
  
  $result['status'] = 1;
  $result['data'] = $exdata;
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

  // $sql = "select * from pet_test_vaccine where customerid = $customer[id] and typeid = $data->typeid and cometime = $data->cometime and calltime = $data->calltime";
  // $v = $db->fetch($sql);

  // if (!empty($v)) $result['messenger'] = 'Phiếu nhắc cùng loại đã được thêm';
  // else {
    $sql = "insert into pet_test_vaccine (customerid, typeid, cometime, calltime, note, status, called, recall, userid, time) values ($customer[id], $data->typeid, $data->cometime, $data->calltime, '', 0, 0, $data->calltime, $userid, ". time() .")";
    $result['status'] = 1;
    $result['vid'] = $db->insertid($sql);
    $result['new'] = getlist(true);
    $result['list'] = getlist();
    $result['old'] = getOlder($customer['id'], $result['vid']);
  // }

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

function remove() {
  global $data, $db, $result;
  $sql = "update pet_test_type set active = 0 where id = $data->id";
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

  $start = strtotime(date('Y/m/d'));

  if ($today) {
    $sql = "select a.*, c.first_name as doctor, b.name, b.phone, b.address, d.name as type from pet_test_vaccine a inner join pet_users c on a.userid = c.userid inner join pet_test_customer b on a.customerid = b.id inner join pet_test_type d on a.typeid = d.id where (a.time between $start and ". time() . ") $xtra and a.status < 2 order by a.id desc limit 50";
    $list = dataCover($db->all($sql));
  }
  else if (empty($data->keyword)) {
    $list = array();

    // Lấy danh 
    $list = array_merge($list, getOver());
    // Lấy danh sách hiện tại theo status
    for ($i = 0; $i <= 2; $i++) { 
      $list = array_merge($list, getCurrent($i));
    }
  }
  else {
    $key = $data->keyword;
    $sql = "select a.*, c.first_name as doctor, b.name, b.phone, b.address, d.name as type from pet_test_vaccine a inner join pet_users c on a.userid = c.userid inner join pet_test_customer b on a.customerid = b.id inner join pet_test_type d on a.typeid = d.id where (b.name like '%$key%' or b.phone like '%$key%') order by a.calltime desc, a.recall desc limit 50";
    $list = dataCover($db->all($sql));
  }

  return $list;
}

function getCurrent($status) {
  global $db, $data, $xtra;

  $time = time();
  $limf = $time;
  $lime = $time + 60 * 60 * 24 * 3;
  $sql = "select a.*, c.first_name as doctor, b.name, b.phone, b.address, d.name as type from pet_test_vaccine a inner join pet_users c on a.userid = c.userid inner join pet_test_customer b on a.customerid = b.id inner join pet_test_type d on a.typeid = d.id where  a.status = $status and (calltime between $limf and $lime) $xtra order by a.recall asc";
  // echo "$sql;<br>";
  return dataCover($db->all($sql));
}

function getOver() {
  global $db, $data, $xtra;

  $time = time();
  $lim = $time;
  $sql = "select a.*, c.first_name as doctor, b.name, b.phone, b.address, d.name as type from pet_test_vaccine a inner join pet_users c on a.userid = c.userid inner join pet_test_customer b on a.customerid = b.id inner join pet_test_type d on a.typeid = d.id where status < 3 and calltime < $lim $xtra order by a.recall asc";
  // echo "$sql;<br>";
  return dataCover($db->all($sql), 1);
}

function getOverList() {
  global $db, $data;

  $time = time() - 60 * 60 * 24;
  $sql = "select a.*, c.first_name as doctor, b.name, b.phone, b.address, d.name as type from pet_test_vaccine a inner join pet_users c on a.userid = c.userid inner join pet_test_customer b on a.customerid = b.id inner join pet_test_type d on a.typeid = d.id where a.status < 3 and calltime < $time order by a.calltime asc limit 50";
  return dataCover($db->all($sql));
}

function dataCover($list, $over = 0) {
  global $start;
  $limit = time() - 60 * 60 * 24 * 7;
  $v = array();
  $stoday = strtotime(date('Y/m/d'));
  $etoday = $stoday + 60 * 60 * 24  - 1;

  foreach ($list as $row) {
    // thời gian gọi
    if (!$row['called']) $called = '-';
    else if ($row['called'] >= $stoday && $row['called'] <= $etoday) $called = 'Hôm hay đã gọi';
    else $called = date('d/m/Y', $row['called']);
    $v []= array(
      'id' => $row['id'],
      'note' => $row['note'],
      'doctor' => $row['doctor'],
      'customerid' => $row['customerid'],
      'name' => $row['name'],
      'phone' => $row['phone'],
      'address' => $row['address'],
      'status' => $row['status'],
      'over' => $over,
      'vaccine' => $row['type'],
      'called' => $called,
      'cometime' => date('d/m/Y', $row['cometime']),
      'calltime' => date('d/m/Y', $row['calltime']),
    );
  }
  return $v;
}

function gettemplist($today = false) {
  global $db, $data;
  $userid = checkUserid();

  $sql = "select * from pet_test_user_per where userid = $userid and module = 'vaccine'";
  $role = $db->fetch($sql);

  $xtra = '';
  if ($role['type'] == 1) $xtra = " and userid = $userid ";

  $sql = "select a.*, c.first_name as doctor, d.name as type from pet_test_vaccine a inner join pet_users c on a.userid = c.userid inner join pet_test_type d on a.typeid = d.id where a.status = 5 $xtra order by a.id desc limit 50";
  $v = $db->all($sql);
  $e = array();
  $l = array();

  foreach ($v as $row) {
    if ($row['customerid']) {
      $sql = "select * from pet_test_customer where id = $row[customerid]";
      $c = $db->fetch($sql);
      $name = $c['name'];
      $phone = $c['phone'];
      $address = $c['address'];
    }
    else {
      $name = '';
      $phone = '';
      $address = '';
    }
    $temp = array(
      'id' => $row['id'],
      'note' => $row['note'],
      'doctor' => $row['doctor'],
      'customerid' => $row['customerid'],
      'name' => $name,
      'phone' => $phone,
      'address' => $address,
      'vaccine' => $row['type'],
      'called' => ($row['called'] ? date('d/m/Y', $row['called']) : ''),
      'cometime' => date('d/m/Y', $row['cometime']),
      'calltime' => ($row['calltime'] ? date('d/m/Y', $row['calltime']) : ''),
    );
    if (empty($phone) || !$row['calltime']) $e []= $temp;
    else $l []= $temp;
  }

  return array_merge($e, $l);
}

function getOlder($customerid, $vid) {
  global $db;

  $sql = "select a.*, c.first_name as doctor, d.name as type, b.phone, b.name, b.address from pet_test_vaccine a inner join pet_users c on a.userid = c.userid inner join pet_test_customer b on a.customerid = b.id inner join pet_test_type d on a.typeid = d.id where a.status < 2 and a.customerid = $customerid and a.id <> $vid order by a.id asc";
  return dataCover($db->all($sql));
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
