<?php

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");

define('ROOTDIR', pathinfo(str_replace(DIRECTORY_SEPARATOR, '/', __file__), PATHINFO_DIRNAME));
include_once(ROOTDIR . '/config.php');
include_once(ROOTDIR . '/server/db.php');
include_once(ROOTDIR . '/server/global.php');
$db = new database($config['servername'], $config['username'], $config['password'], $config['database']);

$sql = "select a.*, c.name, c.phone from b3 a inner join b2 b on a.petid = b.id inner join b1 c on b.customerid = c.id";
$list = $db->all($sql);

foreach ($list as $row) {
  $sql = "select a.*, b.name, b.phone from a2 a inner join a1 b on a.customerid = b.id where b.phone = '$row[phone]' and a.calltime = $row[calltime] and a.typeid = $row[diseaseid]";
  if (empty($db->fetch($sql))) {
    $sql = "select * from pet_test_customer where phone = '$row[phone]'";
    if (empty($c = $db->fetch($sql))) {
      $sql = "insert into pet_test_customer (name, phone) values('$row[name]', '$row[phone]')";
      $c = array('id' => $db->insertid($sql));
    }

    if ($row['status'] == 1) $row['status'] = 2;
    else if ($row['status'] == 2) $row['status'] = 3;

    echo "insert into pet_test_vaccine (customerid, typeid, cometime, calltime, note, status, recall, userid, time, called) values ($c[id], $row[diseaseid], $row[cometime], $row[calltime], '$row[note]', $row[status], $row[calltime], $row[doctorid], $row[ctime], 0); <br>";
  }
}