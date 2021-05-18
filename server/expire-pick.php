<?php 

$name = parseGetData('name', '');

require_once(ROOTDIR .'/expire.php');
$expire = new Expire();

$sql = 'select * from `pet_'. $expire->table .'_catalog` where name = "'. $name .'" limit 1';
$query = $expire->db->query($sql);

if (empty($row = $query->fetch_assoc())) {
  $sql = 'insert into `pet_'. $expire->table .'_catalog` (code, name, unit, categoryid, price) values("", "'. $name .'", "", 0, 0)';
  $expire->db->query($sql);
  $rid = $expire->db->insert_id;
}
else {
  $rid = $row['id'];
}

$result['status'] = 1;
$result['id'] = $rid;
