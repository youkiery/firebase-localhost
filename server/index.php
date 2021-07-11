<?php

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");

define('ROOTDIR', pathinfo(str_replace(DIRECTORY_SEPARATOR, '/', __file__), PATHINFO_DIRNAME));
$inputJSON = file_get_contents('php://input');
$data = json_decode($inputJSON);

$result = array(
  'status' => 0
);
if (empty($data->type) || !file_exists(ROOTDIR. "/$data->type.php")) $result['messenger'] = 'Lỗi chức năng';
else {
  include_once('../config.php');
  include_once('db.php');
  include_once(ROOTDIR. "/$data->type.php");

  $db = new database($config['servername'], $config['username'], $config['password'], $config['database']);
  
  $action = $data->action;
  if (function_exists($action)) $result = $action();
}

echo json_encode($result);
die();