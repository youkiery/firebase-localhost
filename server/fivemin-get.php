<?php 

require_once(ROOTDIR .'/fivemin.php');
$fivemin = new Fivemin();

$id = parseGetData('id', 0);

$sql = 'select a.gopy, concat(b.last_name, " ", b.first_name) as nguoigopy from pet_test_5min a inner join pet_users b on a.nhanvien = b.userid where a.id = '. $id;
$query = $mysqli->query($sql);
$data = $query->fetch_assoc();

$result['status'] = 1;
$result['gopy'] = $data;
$result['data'] = $fivemin->get($id);
