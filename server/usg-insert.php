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
  'status' => parseGetData('status', 0)
);

// thay đổi thông tin khách
$sql = 'select * from `pet_'. $usg->table .'_customer` where phone = "'. $data['phone'] .'"';
$query = $mysqli->query($sql);
$row = $query->fetch_assoc();

if (empty($row)) {
  // insert khách hàng 
  $sql = 'insert into `pet_'. $usg->table .'_customer` (name, phone, address) values("'. $data['customer'] .'", "'. $data['phone'] .'", "")';
  $mysqli->query($sql);
  $row['id'] = $mysqli->insert_id;
}
else {
  $sql = 'update `pet_'. $usg->table .'_customer` set name = "'. $data['customer'] .'" where phone = "'. $data['phone'] .'"';
  $mysqli->query($sql);
}

// Kiểm tra thông tin thú cưng
$sql = 'select * from `pet_'. $usg->table .'_pet` where id = "'. $data['pet'] .'"';
$query = $mysqli->query($sql);
$pet = $query->fetch_assoc();

if (empty($pet)) {
  $sql = 'insert into `pet_'. $usg->table .'_pet` (name, customerid) values("Không biết tên", "'. $row['id'] .'")';
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

$sql = "INSERT INTO `" . $usg->prefix . "_usg2` (petid, doctorid, usgtime, expecttime, expectnumber, vaccinetime, image, status, note, time) VALUES ($data[pet], $userid, $data[cometime], $data[calltime], $data[number], 0, '', 0, '$data[note]', " . time() . ")";
$mysqli->query($sql);

$result['status'] = 1;
