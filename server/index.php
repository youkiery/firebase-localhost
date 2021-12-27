<?php

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");

/*
chuyển đổi petid trong bảng thành customerid
include_once('../config.php');
include_once('db.php');

$db = new database($config['servername'], $config['username'], $config['password'], $config['database']);

$sql = "select a.id, c.id as customerid from pet_test_vaccine a inner join pet_test_pet b on a.petid = b.id inner join pet_test_customer c on b.customerid = c.id";
$list = $db->all($sql);
foreach ($list as $row) {
  $sql = "update pet_test_vaccine set customerid = $row[customerid] where id = $row[id]";
  $db->query($sql);
}

die();
*/

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

define('ROOTDIR', pathinfo(str_replace(DIRECTORY_SEPARATOR, '/', __file__), PATHINFO_DIRNAME));
define('DIR', str_replace('/server', '/', ROOTDIR));
date_default_timezone_set('Asia/Ho_Chi_Minh');
$inputJSON = file_get_contents('php://input');
$data = json_decode($inputJSON);
if (!empty($_POST['action']) && !empty($_POST['type']) && !empty($_POST['session'])) $data = (object) $_POST;

$result = array(
  'status' => 0
);
if (empty($data->type) || !file_exists(ROOTDIR. "/$data->type.php")) $result['messenger'] = 'Lỗi chức năng';
else {
  include_once('../config.php');
  include_once('db.php');
  include_once('global.php');
  include_once(ROOTDIR. "/$data->type.php");

  $db = new database($config['servername'], $config['username'], $config['password'], $config['database']);
  $allow = array('session', 'login', 'signip');

  if (in_array($data->action, $allow) == false) {
    if ($data->type !== 'user') include_once(ROOTDIR. "/user.php");
    // if (check()) {
    //   $result['nogin'] = true;
    //   echo json_encode($result);
    //   die();
    // }
  }

  $sql = "select * from pet_test_config where module = 'version'";
  if (empty($v = $db->fetch($sql))) {
    $sql = "insert into pet_test_config (module, name, value) values('version', '$data->version', '')";
    $db->query($sql);
    $v = array('name' => $data->version);
  }

  if ($v['name'] != $data->version) {
    $result['outdate'] = true;
    $result['link'] = $v['name'];
    echo json_encode($result);
    die();
  }  

  $action = $data->action;
  if (function_exists($action)) $result = $action();
}

echo json_encode($result);
die();