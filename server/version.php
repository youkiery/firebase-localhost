<?php 


$sql = 'select * from `pet_config` where config_name = "thanhxuanversion"';
$query = $mysqli->query($sql);
$row = $query->fetch_assoc();

$result['status'] = 1;
$result['version'] = $row['config_value'];
$result['link'] = 'https://vetgroup.petcoffee.com/upload/app'. $row['config_value']. '.apk';

echo json_encode($result);
die();