<?php 

$key = parseGetData('name', '');

require_once(ROOTDIR .'/expire.php');
$expire = new Expire();

$sql = 'select * from `pet_'. $expire->table .'_catalog` where name like "%'. $key .'%" limit 30';

$query = $expire->db->query($sql);
$data = array();

while ($row = $query->fetch_assoc()) {
  $data []= array(
    'id' => $row['id'],
    'name' => $row['name']
  );
}

$result['status'] = 1;
$result['s'] = $sql;
$result['data'] = $data;
