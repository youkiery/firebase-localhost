<?php 

require_once(ROOTDIR .'/vaccine.php');
$vaccine = new Vaccine();

$data = array(
  'text' => parseGetData('text', ''),
  'id' => parseGetData('id', ''),
);

$filter = array(
  'status' => parseGetData('status', 0)
);

$sql = 'update `'. $vaccine->prefix .'` set note = "'. $data['text'] .'" where id = '. $data['id'];
$query = $mysqli->query($sql);

$result['status'] = 1;

echo json_encode($result);
die();
