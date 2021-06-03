<?php 

require_once(ROOTDIR .'/fivemin.php');
$fivemin = new Fivemin();

$id = parseGetData('id', 0);

$sql = 'select * from pet_test_5min where id = '. $id;
$query = $mysqli->query($sql);
$data = $query->fetch_assoc();

$result['status'] = 1;
$result['gopy'] = $data['gopy'];
$result['data'] = $fivemin->get($id);
