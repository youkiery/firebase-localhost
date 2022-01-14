<?php
// 0: 'Nhắc tiêm phòng trước salơ',
// 1: 'Nhắc test Progesterone',
// 2: 'Tư vấn trước sinh',
// 3: 'Ngày sinh',
// 4: 'Nhắc sổ giun lần 1',
// 5: 'Nhắc tiêm vaccine',
// 6: 'Đã gọi tiêm vaccine',
// 7: 'Đã hoàn thành',
// 8: 'Không theo dõi nữa',
// 9: 'Phiếu tạm',
$aday = 60 * 60 * 24;
$cover = array(
  0 => array('s' => 1, 't' => $aday * 30 * 6),
  1 => array('s' => 1, 't' => $aday * 30 * 6),
  2 => array('s' => 3, 't' => $aday * 1), 
  3 => array('s' => 4, 't' => $aday * 7 * 5), 
  4 => array('s' => 5, 't' => $aday * 7 * 6), 
  5 => array('s' => 6, 't' => 0), 
);

function auto() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['list'] = getlist();
  $result['new'] = getlist(true);
  // $result['type'] = gettypeobj();
  // $result['doctor'] = getDoctor();
  $result['temp'] = gettemplist();
  
  return $result;
}

function filter() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['list'] = getlist();
  
  return $result;
}

function gettemplist() {
  global $db, $data;
  $userid = checkuserid();

  $sql = "select * from pet_test_user_per where userid = $userid and module = 'vaccine'";
  $role = $db->fetch($sql);
  $docs = implode(', ', $data->docs);

  $xtra = array();
  if ($role['type'] < 2) $xtra []= " a.userid = $userid ";
  else if (!empty($data->docs)) {
    $xtra []= " a.userid in ($docs) ";
  }
  $sql = "update pet_test_config set value = '$docs' where module = 'docs' and name = '$userid'";
  $db->query($sql);
  $sql = "update pet_test_config set value = '$data->docscover' where module = 'docscover' and name = '$userid'";
  $db->query($sql);
  if (!empty($data->time)) {
    $data->time = isodatetotime($data->time) + 60 * 60 * 24 - 1;
    $xtra []= " a.time < $data->time ";
  }
  if (count($xtra)) $xtra = "and".  implode(" and ", $xtra);
  else $xtra = "";

  $sql = "select a.*, d.id as customerid, d.name, d.phone, d.address, c.name as doctor from pet_test_usg a inner join pet_users c on a.userid = c.userid inner join pet_test_customer d on a.customerid = d.id where a.status = 9 $xtra order by a.id desc";
  $v = $db->all($sql);
  $e = array();
  $l = array();
  $list = array(
    0 => array(), array()
  );

  foreach ($v as $row) {
    $temp = tempdatacover($row);
    if (empty($temp['phone']) || !$row['calltime']) $e []= $temp;
    else $l []= $temp;
  }

  $list[0] = array_merge($l, $e);
  $start = strtotime(date('Y/m/d'));
  $end = $start + 60 * 60 * 24 - 1;
  $sql = "select a.*, d.id as customerid, d.name, d.phone, d.address, c.name as doctor from pet_test_usg a inner join pet_users c on a.userid = c.userid inner join pet_test_customer d on a.customerid = d.id where utemp = 1 and (time between $start and $end) $xtra order by a.id desc";
  $l = $db->all($sql);

  foreach ($l as $row) {
    $list[1] []= tempdatacover($row);
  }
  return $list;
}

function tempdatacover($data) {
  return array(
    'id' => $data['id'],
    'note' => $data['note'],
    'doctor' => $data['doctor'],
    'customerid' => $data['customerid'],
    'name' => $data['name'],
    'phone' => $data['phone'],
    'address' => $data['address'],
    'number' => $data['number'],
    'called' => ($data['called'] ? date('d/m/Y', $data['called']) : ''),
    'cometime' => date('d/m/Y', $data['cometime']),
    'calltime' => ($data['calltime'] ? date('d/m/Y', $data['calltime']) : ''),
    'time' => date('d/m/Y', $data['time']),
  );
}

function getlist($today = false) {
  global $db, $data, $userid;

  $userid = checkuserid();
  $sql = "select * from pet_test_user_per where userid = $userid and module = 'vaccine'";
  $role = $db->fetch($sql);
  $docs = implode(', ', $data->docs);

  $xtra = array();
  if ($role['type'] < 2) $xtra []= " a.userid = $userid ";
  else if (!empty($data->docs)) {
    $xtra []= " a.userid in ($docs) ";
  }
  if (!isset($data->{'docscover'})) $data->docscover = '';

  $sql = "update pet_test_config set value = '$docs' where module = 'docs' and name = '$userid'";
  $db->query($sql);
  $sql = "update pet_test_config set value = '$data->docscover' where module = 'docscover' and name = '$userid'";
  $db->query($sql);

  if (count($xtra)) $xtra = "and".  implode(" and ", $xtra);
  else $xtra = "";

  $start = strtotime(date('Y/m/d'));

  if ($today) {
    // danh sách đã thêm hôm nay
    $sql = "select a.*, c.name as doctor, b.name, b.phone, b.address from pet_test_usg a inner join pet_users c on a.userid = c.userid inner join pet_test_customer b on a.customerid = b.id where (a.time between $start and ". time() . ") $xtra and a.status < 6 order by a.id desc";
    $list = dataCover($db->all($sql));
  }
  else if (empty($data->keyword)) {
    // danh sách nhắc hôm nay
    $list = array(0 => array(), array(), array());
    $lim = strtotime(date('Y/m/d')) + 60 * 60 * 24 * 3 - 1;
    $sql = "select a.*, c.name as doctor, b.name, b.phone, b.address from pet_test_usg a inner join pet_users c on a.userid = c.userid inner join pet_test_customer b on a.customerid = b.id where (a.status > 1 and a.status < 4) $xtra order by a.recall asc";
    $list[0] = dataCover($db->all($sql));
    
    $sql = "select a.*, c.name as doctor, b.name, b.phone, b.address from pet_test_usg a inner join pet_users c on a.userid = c.userid inner join pet_test_customer b on a.customerid = b.id where (a.status > 3 and a.status < 6) $xtra order by a.recall asc";
    $list[1] = dataCover($db->all($sql));
    $sql = "select a.*, c.name as doctor, b.name, b.phone, b.address from pet_test_usg a inner join pet_users c on a.userid = c.userid inner join pet_test_customer b on a.customerid = b.id where a.status = 6 $xtra order by a.recall asc";
  	$list[1] = array_merge($list[1], dataCover($db->all($sql)));
    
    $sql = "select a.*, c.name as doctor, b.name, b.phone, b.address from pet_test_usg a inner join pet_users c on a.userid = c.userid inner join pet_test_customer b on a.customerid = b.id where a.status < 2 $xtra order by a.recall asc";
    $list[2] = dataCover($db->all($sql));
  }
  else {
    // danh sách tìm kiếm khách hàng
    $key = trim($data->keyword);
    $sql = "select a.*, c.name as doctor, b.name, b.phone, b.address from pet_test_usg a inner join pet_users c on a.userid = c.userid inner join pet_test_customer b on a.customerid = b.id where (b.name like '%$key%' or b.phone like '%$key%') and status < 8 order by a.recall desc";
    $list = dataCover($db->all($sql));
  }

  return $list;
}

function dataCover($list) {
  global $start;
  $lim = strtotime(date('Y/m/d')) - 1 + 60 * 60 * 24;
  $v = array();
  $stoday = strtotime(date('Y/m/d'));
  $etoday = $stoday + 60 * 60 * 24  - 1;

  foreach ($list as $row) {
    // thời gian gọi
    if (!$row['called']) $called = '-';
    else if ($row['called'] >= $stoday && $row['called'] <= $etoday) $called = 'Hôm hay đã gọi';
    else $called = date('d/m/Y', $row['called']);
    // nếu status < 6, kiểm tra recall < lim hay không
    // nếu không thì bỏ qua
    $over = (($row['status'] < 6 && $row['recall'] < $lim) ? 1 : 0);  
    $v []= array(
      'id' => $row['id'],
      'note' => $row['note'],
      'doctor' => $row['doctor'],
      'customerid' => $row['customerid'],
      'name' => $row['name'],
      'phone' => $row['phone'],
      'address' => $row['address'],
      'number' => $row['number'],
      'status' => $row['status'],
      'over' => $over,
      'called' => $called,
      'cometime' => date('d/m/Y', $row['cometime']),
      'calltime' => date('d/m/Y', $row['calltime']),
      'recall' => date('d/m/Y', $row['recall']),
    );
  }
  return $v;
}

function search() {
  global $data, $db, $result;
  $result['status'] = 1;
  $result['list'] = getlist();

  return $result;
}

function searchcustomer() {
  global $data, $db, $result;
  $result['status'] = 1;
  $result['list'] = getlist();

  return $result;
}

function insert() {
  global $data, $db, $result;

  $customerid = checkcustomer();
  
  $data->cometime = isodatetotime($data->cometime);
  $data->calltime = isodatetotime($data->calltime);
  $userid = checkuserid();
  if (!isset($data->number)) $data->number = 0;

  // nếu số con > 0, đặt trạng thái sắp sinh, ngày nhắc là 1 tuần trước sinh
    // nếu không, đặt 5 tháng sau nhắc kỳ salơ
  $recall = $data->cometime + 60 * 60 * 24 * 30 * 5; // mặc định 5 tháng sau salơ
  if ($data->number) $recall = $data->calltime - 60 * 60 * 24 * 7; // có con, nhắc trước ngày sinh 1 tuần
  $status = intval(boolval($data->number)) * 2; // nếu có con thì trạng thái = 1, nếu không, trạng thái = 0

  $sql = "insert into pet_test_usg (customerid, userid, cometime, calltime, recall, number, status, note, time, called) values ($customerid, $userid, $data->cometime, $data->calltime, $recall, $data->number, $status, '$data->note', ". time() .", 0)";
  $result['status'] = 1;
  $result['vid'] = $db->insertid($sql);
  $result['list'] = getlist();
  $result['new'] = getlist(true);
  $result['messenger'] = "Đã thêm vào danh sách nhắc";
  return $result;
}

function uncalled() {
  global $data, $db, $result;

  $sql = "select * from pet_test_usg where id = $data->id";
  $v = $db->fetch($sql);
  $time = time();
  $recall = $v['recall'] + 60 * 60 * 24;
  if ($time > $v['recall']) $recall = $time + 60 * 60 * 24;

  $sql = "update pet_test_usg set note = '". $data->note ."', called = $time, recall = $recall where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['messenger'] = "Đã chuyển dời ngày nhắc đến ". date('d/m/Y', $recall);
  $result['list'] = getlist();
  
  return $result;
}

function update() {
  global $data, $db, $result;

  $customerid = checkcustomer();
  
  $data->cometime = isodatetotime($data->cometime);
  $data->calltime = isodatetotime($data->calltime);

  // nếu số con > 0, đặt trạng thái sắp sinh, ngày nhắc là 1 tuần trước sinh
  // nếu không, đặt 5 tháng sau nhắc kỳ salơ
  $recall = $data->come + 60 * 60 * 24 * 30 * 5; // mặc định 5 tháng sau salơ
  if ($data->number) $recall = $data->calltime - 60 * 60 * 24 * 7; // có con, nhắc trước ngày sinh 1 tuần
  $sql = "select * from pet_test_usg where id = $data->id";
  $u = $db->fetch($sql);
  $status = intval(boolval($data->number)); // nếu có con thì trạng thái = 1, nếu không, trạng thái = 0
  // kiểm tra kết quả status bên trên có thay đổi hay không
  // nếu có, reset, nếu không, giữ nguyên
  if ($status == 1 && $u['status'] < 2) $status = 2; // trạng thái chưa sinh
  else if ($status == 0 && $u['status'] > 1) $status = 0; // trạng thái nhắc tiêm phòng salơ
  else $status = $u['status'];
  
  $sql = "update pet_test_usg set customerid = $customerid, note = '$data->note', cometime = $data->cometime, calltime = $data->calltime, recall = $recall, status = $status, number = $data->number where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['list'] = getlist();
  $result['new'] = getlist(true);
  $result['messenger'] = "Đã cập nhật phiếu nhắc";
  return $result;
}

function birth() {
  global $data, $db, $result, $cover;

  // nếu có ngày nhắc ngày salơ chó mẹ
  if (!empty($data->repregnant)) {
    $time = explode('-', $data->repregnant);
    $data->repregnant = totime("$time[2]/$time[1]/$time[0]");
    $recall = $data->repregnant + 60 * 60 * 24 * 7 * 5;

    $userid = checkuserid();
    $time = time();
    $sql = "select customerid from pet_test_usg where id = $data->id";
    $usg = $db->fetch($sql);

    $sql = "insert into pet_test_usg (customerid, userid, cometime, calltime, recall, number, status, note, time, called) values ($usg[customerid], $userid, $time, $recall, $recall, 0, 0, '$data->note', $time, 0)";
    $db->query($sql);
  }

  $time = explode('-', $data->calltime);
  $data->calltime = totime("$time[2]/$time[1]/$time[0]");
  $recall = $data->calltime + 60 * 60 * 24 * 7 * 5;
  $status = 4;

  $sql = "update pet_test_usg set status = $status, note = '". $data->note ."', number = $data->number, calltime = $data->calltime, recall = $recall where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['messenger'] = "Đã thay đổi trạng thái";
  $result['list'] = getlist();

  return $result;
}

function called() {
  global $data, $db, $result, $cover;

  $sql = "select * from pet_test_usg where id = $data->id";
  $u = $db->fetch($sql);
  $time = time();
  if ($u['status'] == 0) $recall = $u['cometime'] + $cover[$u['status']]['t'];
  else $recall = $u['calltime'] + $cover[$u['status']]['t'];
  $status = $cover[$u['status']]['s'];

  $sql = "update pet_test_usg set status = $status, note = '". $data->note ."', called = $time, recall = $recall where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['messenger'] = "Đã thay đổi trạng thái";
  $result['list'] = getlist();

  return $result;
}

function dead() {
  global $data, $db, $result;

  $sql = "update pet_test_usg set status = 8, note = '$data->note' where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['messenger'] = "Phiếu nhắc đã không được theo dõi nữa";
  $result['list'] = getlist();
  
  return $result;
}

function done() {
  global $data, $db, $result;

  $sql = "update pet_test_usg set status = 7, note = '$data->note' where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['messenger'] = "Phiếu nhắc đã không được theo dõi nữa";
  $result['list'] = getlist();
  
  return $result;
}

function repregnant() {
  global $data, $db, $result;

  $time = time();
  $recall = $time + 60 * 60 * 24 * 30 * 5;
  $sql = "update pet_test_usg set status = 0, cometime = $time, calltime = $recall, recall = $recall, note = '$data->note' where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['messenger'] = "Phiếu nhắc đã lặp lại 5 tháng sau";
  $result['list'] = getlist();
  
  return $result;
}

function checkcustomer() {
  global $db, $data;

  $sql = "select * from pet_test_customer where phone = '$data->phone'";
  if (!empty($customer = $db->fetch($sql))) {
    $sql = "update pet_test_customer set name = '$data->name', address = '$data->address' where id = $customer[id]";
    $db->query($sql);
  }
  else {
    $sql = "insert into pet_test_customer (name, phone, address) values ('$data->name', '$data->phone', '$data->address')";
    $customer['id'] = $db->insertid($sql);
  }

  return $customer['id'];
}

function tempauto() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['list'] = gettemplist();
  
  return $result;
}

function typeauto() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['list'] = gettypeobj();
  
  return $result;
}

function doctorauto() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['list'] = getDoctor();
  
  return $result;
}

function removeall() {
  global $data, $db, $result;

  foreach ($data->list as $id) {
    $sql = "delete from pet_test_usg where id = $id";
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['messenger'] = "Đã xóa các phiếu nhắc tạm";
  $result['list'] = gettemplist();
  return $result;
}

function doneall() {
  global $data, $db, $result;

  $userid = checkuserid();
  $time = time();
  foreach ($data->list as $id) {
    $sql = "select a.number, a.calltime, a.cometime, b.* from pet_test_usg a inner join pet_test_pet b on a.customerid = b.id where a.id = $id";
    $u = $db->fetch($sql);
    // nếu số con > 0, đặt trạng thái sắp sinh, ngày nhắc là 1 tuần trước sinh
    // nếu không, đặt 5 tháng sau nhắc kỳ salơ
    $recall = $u['cometime'] + 60 * 60 * 24 * 30 * 5; // mặc định 5 tháng sau salơ
    if ($u['number']) $recall = $u['calltime'] - 60 * 60 * 24 * 7; // có con, nhắc trước ngày sinh 1 tuần
    $status = intval(boolval($u['number'])) * 2; // nếu có con thì trạng thái = 1, nếu không, trạng thái = 0

    $sql = "update pet_test_usg set status = $status, recall = $recall, utemp = 1, time = $time where id = $id";
    $db->query($sql);
  }

  $result['list'] = gettemplist();
  $result['messenger'] = "Đã xác nhận các phiếu nhắc tạm";
  $result['status'] = 1;
  return $result;
}

function history() {
  global $data, $db, $result;

  $sql = "select a.*, c.name as doctor, b.phone, b.name, b.address from pet_test_usg a inner join pet_users c on a.userid = c.userid inner join pet_test_customer b on a.customerid = b.id where a.status < 3 and b.phone = '$data->phone' order by a.id asc";
  $result['status'] = 1;
  $result['old'] = dataCover($db->all($sql));
  return $result;
}

function updatehistory() {
  global $data, $db, $result;

  $customerid = checkcustomer();
  
  $data->cometime = isodatetotime($data->cometime);
  $data->calltime = isodatetotime($data->calltime);
  $userid = checkuserid();

  // nếu số con > 0, đặt trạng thái sắp sinh, ngày nhắc là 1 tuần trước sinh
  // nếu không, đặt 5 tháng sau nhắc kỳ salơ
  $recall = $data->cometime + 60 * 60 * 24 * 30 * 5; // mặc định 5 tháng sau salơ
  if ($data->number) $recall = $data->calltime - 60 * 60 * 24 * 7; // có con, nhắc trước ngày sinh 1 tuần
  $status = intval(boolval($data->number)) * 2; // nếu có con thì trạng thái = 1, nếu không, trạng thái = 0

  $sql = "update pet_test_usg set customerid = $customerid, cometime = $data->cometime, calltime = $data->calltime, status = $status, recall = $recall, note = '$data->note', number = $data->number, utemp = 1, time = ". time() ." where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['messenger'] = 'Đã xác nhận và thêm vào danh sách nhắc';
  $result['list'] = gettemplist();

  return $result;
}

function transfer() {
  global $data, $db, $result;

  foreach ($data->list as $id) {
    $sql = "update pet_test_usg set userid = $data->uid where id = $id";
    $db->query($sql);
  }
  $sql = "select a.userid, b.name from pet_test_user_per a inner join pet_users b on a.userid = b.userid where a.module = 'doctor' and a.type = 1 and a.userid = $data->uid";
  $d = $db->fetch($sql);
  $result['status'] = 1;
  $result['messenger'] = "Đã chuyển phiếu nhắc sang cho nhân viên: $d[name]";
  $result['list'] = gettemplist();

  return $result;
}

function confirm() {
  global $data, $db, $result;

  $sql = "select a.*, c.name as doctor, b.name, b.phone, b.address from pet_test_usg a inner join pet_users c on a.userid = c.userid inner join pet_test_customer b on a.customerid = b.id where a.id = $data->id";
  $c = $db->fetch($sql);

  // nếu số con > 0, đặt trạng thái sắp sinh, ngày nhắc là 1 tuần trước sinh
  // nếu không, đặt 5 tháng sau nhắc kỳ salơ
  $recall = $c['cometime'] + 60 * 60 * 24 * 30 * 5; // mặc định 5 tháng sau salơ
  if ($c['number']) $recall = $c['calltime'] - 60 * 60 * 24 * 7; // có con, nhắc trước ngày sinh 1 tuần
  $status = intval(boolval($c['number'])) * 2; // nếu có con thì trạng thái = 1, nếu không, trạng thái = 0
  $userid = checkuserid();

  $sql = "update pet_test_usg set status = $status, utemp = 1, recall = $recall, time = ". time() ." where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['messenger'] = "Đã xác nhận và chuyển vào danh sách nhắc";
  $result['temp'] = gettemplist();

  return $result;
}

function donerecall() {
  global $data, $db, $result;

  foreach ($data->list as $id) {
    $sql = "update pet_test_usg set status = 3 where id = $id";
    $db->query($sql);
  }
  $result['status'] = 1;
  $result['messenger'] = "Tất cả phiếu nhắc được chọn chuyển thành 'Đã tái chủng'";
  $result['new'] = getlist(true);
  return $result;
}

function removetemp() {
  global $data, $db, $result;
  $sql = "delete from pet_test_usg where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['list'] = gettemplist();
  $result['messenger'] = "Đã xóa phiếu tạm";
  return $result;
}

function removetype() {
  global $data, $db, $result;

  $sql = "update pet_test_config set module = 'usg-deleted' where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = gettypelist();
  return $result;
}

function updatetype() {
  global $data, $db, $result;

  $sql = "update pet_test_config set name = '$data->name' where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = gettypelist();
  return $result;
}

function inserttype() {
  global $data, $db, $result;

  $sql = "insert into pet_test_config (module, name, value) values('usg', '$data->name', '')";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = gettypelist();
  return $result;
}

function gettypelist() {
  global $db;

  $sql = "select id, name from pet_test_config where module = 'usg'";
  return $db->all($sql);
}
