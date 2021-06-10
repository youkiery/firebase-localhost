<?php 

require_once(ROOTDIR .'/usg.php');
$usg = new Usg();

$data = array(
  'customer' => parseGetData('customer'),
  'phone' => parseGetData('phone'),
  'pet' => parseGetData('pet'),
  'number' => parseGetData('number'),
  'cometime' => parseGetData('cometime'),
  'calltime' => parseGetData('calltime'),
  'note' => parseGetData('note')
);
$data['cometime'] = totime($data['cometime']);
$data['calltime'] = totime($data['calltime']);

$filter = array(
  'status' => parseGetData('status', 0),
  'keyword' => parseGetData('keyword', '')
);

// thay đổi thông tin khách
$sql = 'select * from `pet_test_customer` where phone = "'. $data['phone'] .'"';
$query = $mysqli->query($sql);
$row = $query->fetch_assoc();

if (empty($row)) {
  // insert khách hàng 
  $sql = 'insert into `pet_test_customer` (name, phone, address) values("'. $data['customer'] .'", "'. $data['phone'] .'", "")';
  $mysqli->query($sql);
  $row['id'] = $mysqli->insert_id;
}
else {
  $sql = 'update `pet_test_customer` set name = "'. $data['customer'] .'" where phone = "'. $data['phone'] .'"';
  $mysqli->query($sql);
}

// Kiểm tra thông tin thú cưng
$sql = 'select * from `pet_test_pet` where id = "'. $data['pet'] .'"';
$query = $mysqli->query($sql);
$pet = $query->fetch_assoc();

if (empty($pet)) {
  $sql = 'insert into `pet_test_pet` (name, customerid) values("Không biết tên", "'. $row['id'] .'")';
  $query = $mysqli->query($sql);
  $pet['id'] = $mysqli->insert_id;
}

// // kiểm tra nếu đã có thì tick luôn
// $sql = 'select * from `'. $usg->prefix .'` where diseaseid = '. $data['disease'] .' and petid = '. $pet['id'] .' and status < 2 order by calltime desc limit 1';
// $query = $mysqli->query($sql);

// if (!empty($row = $query->fetch_assoc())) {
//   $sql = 'update `'. $usg->prefix .'` set status = 2, recall = '. $data['cometime'] .' where diseaseid = '. $data['disease'] .' and petid = '. $pet['id'] .' and status < 2';
//   $mysqli->query($sql);
// }

$sql = "INSERT INTO `pet_test_usg2` (petid, doctorid, usgtime, expecttime, expectnumber, vaccinetime, image, status, note, time) VALUES ($data[pet], $userid, $data[cometime], $data[calltime], $data[number], 0, '', 0, '$data[note]', " . time() . ")";

$mysqli->query($sql);

$start = strtotime(date('Y/m/d'));
$end = time();

$sql = 'select * from pet_test_usg2 where (time between '. $start . ' and '. $end . ') and status = 0 limit 50';
$query = $mysqli->query($sql);

$data = array();
// tên thú cưng, sđt, vaccine, ngày tái chủng, ghi chú, trạng thại
while ($row = $query->fetch_assoc()) {
  $pet = $usg->getPetId($row['petid']);
  $customer = $usg->getCustonerId($pet['customerid']);
  if (!empty($customer['phone'])) {
    $data []= array(
      'name' => $customer['name'],
      'number' => $customer['phone'],
      'time' => date('d/m/Y', $row['usgtime']),
      'calltime' => date('d/m/Y', $row['expecttime']),
    );
  }
}

$result['status'] = 1;
$result['data'] = $usg->getList($filter);
$result['new'] = $data;
