<?php
$spa_option = array(
  "wash_dog" => "Tắm chó",
  "wash_cat" => "Tắm mèo",
  "wash_white" => "Tắm trắng",
  "cut_fur" => "Cắt lông",
  "shave_foot" => "Cạo lông chân",
  "shave_fur" => "Cạo ông",
  "cut_claw" => "Cắt, dũa móng",
  "cut_curly" => "Cắt lông rối",
  "wash_ear" => "Vệ sinh tai",
  "wash_mouth" => "Vệ sinh răng miệng",
  "paint_footear" => "Nhuộm chân, tai",
  "paint_all" => "Nhuộm toàn thân",
  "pin_ear" => "Bấm lỗ tai",
  "cut_ear" => "Cắt lông tai",
  "dismell" => "Vắt tuyết hôi"
);

$status = array("Chưa xong", "Đã xong");

function auto() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['list'] = getlist();
  return $result;
}

function getList() {
  global $data, $db, $spa_option;

  $time = strtotime(date('Y/m/d', $data->time / 1000));
  $end = $time + 60 * 60 * 24 - 1;
  $sql = 'select a.*, b.name, b.phone, c.first_name as user from `pet_test_spa` a inner join pet_test_customer b on a.customerid = b.id inner join pet_users c on a.doctorid = c.userid where time between '. $time .' and '. $end;
  $spa = $db->all($sql);

  $list = array();
  foreach ($spa as $row) {
    $option = array();
    $service = array();
    foreach ($spa_option as $key => $value) {
      if ($row[$key]) $service []= $spa_option[$key];
      $option[$key] = $row[$key];
    }
    $image = explode(', ', $row['image']);
    $list []= array(
      'id' => $row['id'],
      'name' => $row['name'],
      'phone' => $row['phone'],
      'user' => $row['user'],
      'note' => $row['note'],
      'image' => (count($image) && !empty($image[0]) ? $image : array()),
      'time' => date('d/m/Y', $row['time']),
      'option' => $option,
      'service' => implode(', ', $service)
    );
  }

  return $list;
}

function insert() {
  global $data, $db, $result;

  $name = array();
  $value = array();
  foreach ($data->option as $n => $v) {
    $name[] = $n;
    $value[] = $v;
  }
  $userid = checkUserid();

  $sql = "select * from pet_test_customer where phone = '$data->phone'";
  if (!empty($customer = $db->fetch($sql))) {
    $sql = "update pet_test_customer set name = '$data->name' where id = $customer[id]";
    $db->query($sql);
  }
  else {
    $sql = "insert into pet_test_customer (name, phone, address) values ('$data->name', '$data->phone', '')";
    $customer['id'] = $db->insertid($sql);
  }

  $sql = "insert into pet_test_spa (customerid, doctorid, note, time, " . implode(", ", $name) . ", image) values($customer[id], $userid, '$data->note', '" . time() . "', " . implode(", ", $value) . ", '". str_replace('@@', '%2F', implode(', ', $data->image))."')";
  $db->query($sql);  

  $result['status'] = 1;
  return $result;
}