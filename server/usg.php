<?php
// 0: 'Nhắc tới ngày sa lơ',
// 1: 'Tư vấn trước sinh',
// 2: 'Ngày sinh',
// 3: 'Nhắc sổ giun lần 1',
// 4: 'Nhắc sổ giun lần 2',
// 5: 'Nhắc tiêm vaccine',
// 6: 'Đã hoàn thành',
// 7: 'Không theo dõi nữa',
// 8: 'Phiếu tạm',
$aday = 60 * 60 * 24;
$cover = array(
  0 => array('s' => 6, 't' => 0),
  1 => array('s' => 2, 't' => $aday * -1), 
  2 => array('s' => 3, 't' => $aday * 7 * 3), 
  3 => array('s' => 4, 't' => $aday * 7 * 5), 
  4 => array('s' => 5, 't' => $aday * 7 * 6), 
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

function gettemplist() {
  global $db, $data;
  $userid = checkUserid();

  $sql = "select * from pet_test_user_per where userid = $userid and module = 'usg'";
  $role = $db->fetch($sql);

  $xtra = array();
  if ($role['type'] < 2) $xtra []= " a.userid = $userid ";
  if (!empty($data->docs)) $xtra []= " a.userid in (". implode(', ', $data->docs) .") ";
  if (!empty($data->time)) {
    $data->time = isodatetotime($data->time) + 60 * 60 * 24 - 1;
    $xtra []= " a.time < $data->time ";
  }
  if (count($xtra)) $xtra = "and".  implode(" and ", $xtra);
  else $xtra = "";

  $sql = "select a.*, d.id as customerid, d.name, d.phone, d.address, c.first_name as doctor from pet_test_usg a inner join pet_users c on a.userid = c.userid inner join pet_test_customer d on a.customerid = d.id where a.status = 8 $xtra order by a.id desc";
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
  $sql = "select a.*, d.id as customerid, d.name, d.phone, d.address, c.first_name as doctor from pet_test_usg a inner join pet_users c on a.userid = c.userid inner join pet_test_customer d on a.customerid = d.id where utemp = 1 and (time between $start and $end) $xtra order by a.id desc";
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
  );
}

function getlist($today = false) {
  global $db, $data, $userid;

  $userid = checkUserid();
  $sql = "select * from pet_test_user_per where userid = $userid and module = 'vaccine'";
  $role = $db->fetch($sql);

  $xtra = array();
  if ($role['type'] < 2) $xtra []= " a.userid = $userid ";
  if (!empty($data->docs)) $xtra []= " a.userid in (". implode(', ', $data->docs) .") ";
  if (count($xtra)) $xtra = "and".  implode(" and ", $xtra);
  else $xtra = "";

  $start = strtotime(date('Y/m/d'));

  if ($today) {
    // danh sách đã thêm hôm nay
    $sql = "select a.*, c.first_name as doctor, b.name, b.phone, b.address from pet_test_usg a inner join pet_users c on a.userid = c.userid inner join pet_test_customer b on a.customerid = b.id where (a.time between $start and ". time() . ") $xtra and a.status < 6 order by a.id desc";
    $list = dataCover($db->all($sql));
  }
  else if (!strlen($data->keyword)) {
    // danh sách nhắc hôm nay
    $lim = strtotime(date('Y/m/d')) - 1 + 60 * 60 * 24 * 3;
    $sql = "select a.*, c.first_name as doctor, b.name, b.phone, b.address from pet_test_usg a inner join pet_users c on a.userid = c.userid inner join pet_test_customer b on a.customerid = b.id where a.status < 6 and recall < $lim order by a.recall asc";
    $list = dataCover($db->all($sql));
  }
  else {
    // danh sách tìm kiếm khách hàng
    $key = trim($data->keyword);
    $sql = "select a.*, c.first_name as doctor, b.name, b.phone, b.address from pet_test_usg a inner join pet_users c on a.userid = c.userid inner join pet_test_customer b on a.customerid = b.id where (b.name like '%$key%' or b.phone like '%$key%') and status < 8 order by a.calltime desc, a.recall desc";
    $list = dataCover($db->all($sql));
  }

  return $list;
}

function dataCover($list) {
  global $start;
  $lim = strtotime(date('Y/m/d')) - 1 + 60 * 60 * 24 * 3;
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
  $userid = checkUserid();

  // nếu số con > 0, đặt trạng thái sắp sinh, ngày nhắc là 1 tuần trước sinh
    // nếu không, đặt 5 tháng sau nhắc kỳ salơ
  $recall = $data->calltime + 60 * 60 * 24 * 30 * 5; // mặc định 5 tháng sau salơ
  if ($data->number) $recall = $data->calltime - 60 * 60 * 24 * 7; // có con, nhắc trước ngày sinh 1 tuần
  $status = intval(boolval($data->number)); // nếu có con thì trạng thái = 1, nếu không, trạng thái = 0

  $sql = "insert into pet_test_usg (customerid, userid, cometime, calltime, recall, number, status, note, time, called) values ($customerid, $userid, $data->cometime, $data->calltime, $recall, $data->number, $status, '$data->note', ". time() .", 0)";
  $result['status'] = 1;
  $result['vid'] = $db->insertid($sql);
  $result['list'] = getlist();
  $result['new'] = getlist(true);
  $result['messenger'] = "Đã thêm vào danh sách nhắc";
  return $result;
}

function update() {
  global $data, $db, $result;

  $customerid = checkcustomer();
  
  $data->cometime = isodatetotime($data->cometime);
  $data->calltime = isodatetotime($data->calltime);

  // nếu số con > 0, đặt trạng thái sắp sinh, ngày nhắc là 1 tuần trước sinh
  // nếu không, đặt 5 tháng sau nhắc kỳ salơ
  $recall = $data->calltime + 60 * 60 * 24 * 30 * 5; // mặc định 5 tháng sau salơ
  if ($data->number) $recall = $data->calltime - 60 * 60 * 24 * 7; // có con, nhắc trước ngày sinh 1 tuần
  $status = intval(boolval($data->number)); // nếu có con thì trạng thái = 1, nếu không, trạng thái = 0
  
  $sql = "update pet_test_usg set customerid = $customerid, note = '$data->note', cometime = $data->cometime, calltime = $data->calltime, recall = $recall, status = $status, number = $data->number where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['list'] = getlist();
  $result['new'] = getlist(true);
  $result['messenger'] = "Đã cập nhật phiếu nhắc";
  return $result;
}

function called() {
  global $data, $db, $result, $cover;

  $sql = "select * from pet_test_usg where id = $data->id";
  $u = $db->fetch($sql);
  $time = time();
  $recall = $u['calltime'] + $cover[$u['status']]['t'];
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

  $sql = "update pet_test_usg set status = 7, note = '$data->note' where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['messenger'] = "Phiếu nhắc đã không được theo dõi nữa";
  $result['list'] = getlist();
  
  return $result;
}

function done() {
  global $data, $db, $result;

  $sql = "update pet_test_usg set status = 6, note = '$data->note' where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['messenger'] = "Phiếu nhắc đã không được theo dõi nữa";
  $result['list'] = getlist();
  
  return $result;
}

function reprenag() {
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

  $userid = checkUserid();
  $time = time();
  foreach ($data->list as $id) {
    $sql = "select a.number, a.calltime, b.* from pet_test_usg a inner join pet_test_pet b on a.customerid = b.id where a.id = $id";
    $u = $db->fetch($sql);
    // nếu số con > 0, đặt trạng thái sắp sinh, ngày nhắc là 1 tuần trước sinh
    // nếu không, đặt 5 tháng sau nhắc kỳ salơ
    $recall = $u['calltime'] + 60 * 60 * 24 * 30 * 5; // mặc định 5 tháng sau salơ
    if ($u['number']) $recall = $u['calltime'] - 60 * 60 * 24 * 7; // có con, nhắc trước ngày sinh 1 tuần
    $status = intval(boolval($u['number'])); // nếu có con thì trạng thái = 1, nếu không, trạng thái = 0

    $sql = "update pet_test_usg set status = $status, recall = $recall, utemp = 1, userid = $userid, time = $time where id = $id";
    $db->query($sql);
  }

  $result['list'] = gettemplist();
  $result['messenger'] = "Đã xác nhận các phiếu nhắc tạm";
  $result['status'] = 1;
  return $result;
}

function history() {
  global $data, $db, $result;

  $sql = "select a.*, c.first_name as doctor, b.phone, b.name, b.address from pet_test_usg a inner join pet_users c on a.userid = c.userid inner join pet_test_customer b on a.customerid = b.id where a.status < 3 and b.phone = '$data->phone' order by a.id asc";
  $result['status'] = 1;
  $result['old'] = dataCover($db->all($sql));
  return $result;
}

function inserthistory() {
  global $data, $db, $result;

  $customerid = checkcustomer();
  
  $data->cometime = isodatetotime($data->cometime);
  $data->calltime = isodatetotime($data->calltime);
  $userid = checkUserid();

  $sql = "update pet_test_usg set status = 3 where id = $data->id";
  $db->query($sql);

  $sql = "insert into pet_test_usg (customerid, typeid, cometime, calltime, note, status, called, recall, userid, time) values ($customerid, $data->typeid, $data->cometime, $data->calltime, '$data->note', 0, 0, $data->calltime, $userid, ". time() .")";
  $db->query($sql);
  $result['status'] = 1;
  $result['messenger'] = 'Đã xác nhận và hoàn thành phiếu nhắc cũ';
  $result['new'] = getlist(true);

  return $result;
}

function updatehistory() {
  global $data, $db, $result;

  $customerid = checkcustomer();
  
  $data->cometime = isodatetotime($data->cometime);
  $data->calltime = isodatetotime($data->calltime);
  $userid = checkUserid();

  // nếu số con > 0, đặt trạng thái sắp sinh, ngày nhắc là 1 tuần trước sinh
  // nếu không, đặt 5 tháng sau nhắc kỳ salơ
  $recall = $data->calltime + 60 * 60 * 24 * 30 * 5; // mặc định 5 tháng sau salơ
  if ($data->number) $recall = $data->calltime - 60 * 60 * 24 * 7; // có con, nhắc trước ngày sinh 1 tuần
  $status = intval(boolval($data->number)); // nếu có con thì trạng thái = 1, nếu không, trạng thái = 0

  $sql = "update pet_test_usg set customerid = $customerid, cometime = $data->cometime, calltime = $data->calltime, status = $status, recall = $recall, note = '$data->note', userid = $userid, number = $data->number, utemp = 1, time = ". time() ." where id = $data->id";
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
  $sql = "select * from pet_test_doctor where userid = $data->uid";
  $d = $db->fetch($sql);
  $result['status'] = 1;
  $result['messenger'] = "Đã chuyển phiếu nhắc sang cho nhân viên: $d[name]";
  $result['list'] = gettemplist();

  return $result;
}

function confirm() {
  global $data, $db, $result;

  $sql = "select a.*, c.first_name as doctor, b.name, b.phone, b.address from pet_test_usg a inner join pet_users c on a.userid = c.userid inner join pet_test_customer b on a.customerid = b.id where a.id = $data->id";
  $c = $db->fetch($sql);

  // nếu số con > 0, đặt trạng thái sắp sinh, ngày nhắc là 1 tuần trước sinh
  // nếu không, đặt 5 tháng sau nhắc kỳ salơ
  $recall = $c['calltime'] + 60 * 60 * 24 * 30 * 5; // mặc định 5 tháng sau salơ
  if ($c['number']) $recall = $c['calltime'] - 60 * 60 * 24 * 7; // có con, nhắc trước ngày sinh 1 tuần
  $status = intval(boolval($c['number'])); // nếu có con thì trạng thái = 1, nếu không, trạng thái = 0
  $userid = checkUserid();

  $sql = "update pet_test_usg set status = $status, utemp = 1, recall = $recall, userid = $userid, time = ". time() ." where id = $data->id";
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

function excel() {
  global $data, $db, $result, $_FILES;

  $dir = str_replace('/server', '/', ROOTDIR);
  // $des = $dir ."export/DanhSachChiTietHoaDon_KV16102021-152929-688-1634373156.xlsx";

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
  $objReader->setReadDataOnly(true);
  $objPHPExcel = $objReader->load($des);
  
  $sheet = $objPHPExcel->getSheet(0); 

  $highestRow = $sheet->getHighestRow(); 
  $highestColumn = $sheet->getHighestColumn();

  $sql = "select * from pet_test_doctor";
  $doctor = $db->obj($sql, 'name', 'userid');

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
    if ($row[0] == 'BVCK01025tpx') {
      $dat = explode(';', $row[5]);
      if (count($dat) >= 2) $number = intval($dat[1]);
      else $number = 0;
      $date = explode('/', $dat[0]);

      if (count($date) == 3) $calltime = strtotime("$date[2]/$date[1]/$date[0]");
      else $calltime = 0;
      
      $sql = "select * from pet_test_customer where phone = '$row[2]'";
      if (empty($c = $db->fetch($sql))) {
        $sql = "insert into pet_test_customer (name, phone, address) values('$row[3]', '$row[2]', '')";
        $c['id'] = $db->query($sql);
      }

      $datetime = explode(' ', $row[4]);
      $date = explode('/', $datetime[0]);
      $cometime = strtotime("$date[2]/$date[1]/$date[0]");

      $sql = "insert into pet_test_usg (customerid, userid, cometime, calltime, recall, number, status, note, time, called) values($c[id], ". $doctor[$row[1]] .", $cometime, $calltime, $calltime, '$number', 8, '', ". time() .", 0)";
      $db->query($sql);
      // echo "$sql <br>";
    }
  }
  $result['list'] = gettemplist();
  $result['messenger'] = "Đã chuyển dữ liệu Excel thành phiếu nhắc";
  return $result;
}

function removevaccine() {
  global $data, $db, $result;
  $sql = "delete from pet_test_usg where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['new'] = getlist(true);
  $result['messenger'] = "Đã xóa phiếu nhắc";
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

function getOlder($customerid, $vid) {
  global $db;

  $sql = "select * from pet_test_pet where id = $customerid";
  $p = $db->fetch($sql);
  $customerid = $p['customerid'];

  $sql = "select a.*, c.first_name as doctor, b.phone, b.name, b.address from pet_test_usg a inner join pet_users c on a.userid = c.userid inner join pet_test_customer b on a.customerid = b.id where a.status < 3 and g.customerid = $customerid and a.id <> $vid order by a.id asc";
  return dataCover($db->all($sql));
}
