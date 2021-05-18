<?php 

require_once(ROOTDIR .'/blood.php');
$blood = new Blood();

$number = parseGetData('number', 0);

$targetid = $blood->check_blood_remind('Chạy hóa chất tự động');
$sample_number = $blood->check_last_blood();
$end = $sample_number - $number;

$sql = 'insert into `' . $blood->prefix . '_row` (time, number, start, end, doctor, target) values(' . time() . ', ' . $number . ', ' . $sample_number . ', ' . $end . ', ' . $user['userid'] . ', ' . $targetid . ')';
$query = $blood->db->query($sql);
if ($query) {
  $query = $blood->db->query('update `pet_config` set config_value = ' . $end . ' where config_name = "' . $blood->table . '_blood_number"');
  // die($sql);
  if ($query) {
    $result['status'] = 1;
    $result['number'] = $blood->check_last_blood();
  }
}