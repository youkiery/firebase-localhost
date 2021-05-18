<?php 

require_once(ROOTDIR .'/blood.php');
$blood = new Blood();

$data = array(
  'number' => parseGetData('number', ''),
  'target' => parseGetData('target', ''),
);

$targetid = $blood->check_blood_remind($data['target']);
$sample_number = $blood->check_last_blood();
$end = $sample_number - $data['number'];

$time = time();

$sql = 'insert into `' . $blood->prefix . '_row` (time, number, start, end, doctor, target) values(' . $time . ', ' . $data['number'] . ', ' . $sample_number . ', ' . $end . ', ' . $userid . ', ' . $targetid . ')';
if ($blood->db->query($sql)) {
  $sql = 'update `pet_config` set config_value = ' . $end . ' where config_name = "' . $blood->table . '_blood_number"';
  $query = $blood->db->query($sql);

  $result['status'] = 1;
  $result['number'] = $blood->check_last_blood();
}
