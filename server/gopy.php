<?php 

$gopy = parseGetData('gopy', '');

$sql = 'insert into pet_test_gopy (userid, noidung) values('. $userid .', "'. $gopy .'")';
$mysqli->query($sql);
$result['status'] = 1;
