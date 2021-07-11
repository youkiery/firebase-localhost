<?php
function auto() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['list'] = getlist();
  $result['new'] = getlist(true);

  return $result;
}

function getlist($today = false) {
  global $db;

  $disease = diseaseList();
  if ($today) {
    $start = strtotime(date('Y/m/d'));
    $end = time();
    $sql = 'select * from pet_test_vaccine where (ctime between '. $start . ' and '. $end . ') and status < 2 limit 50';
  }
  else {
    $end = time() + 60 * 60 * 24 * 7;
    $sql = 'select * from pet_test_vaccine where (calltime < '. $end . ') and status < 2 limit 50';
  }

  $query = $db->query($sql);
  $list = array();

  while ($row = $query->fetch_assoc()) {
    $customer = getCustomer($row['petid']);
    if (!empty($customer['phone'])) {
      $list []= array(
        'id' => $row['id'],
        'name' => $customer['name'],
        'number' => $customer['phone'],
        'vaccine' => $disease[$row['diseaseid']],
        'calltime' => date('d/m/Y', $row['calltime']),
      );
    }
  }

  return $list;
}

function getCustomer($petid) {
  global $db;

  $sql = "select * from pet_test_pet where id = $petid";
  $pet = $db->fetch($sql);

  $sql = "select * from pet_test_customer where id = $pet[customerid]";
  return $db->fetch($sql);
}

function diseaseList() {
  global $db;
  $sql = 'select * from `pet_test_disease`';
  return $db->object($sql, 'id', 'name');
}
