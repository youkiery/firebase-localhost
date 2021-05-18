<?php 

require_once(ROOTDIR .'/blood.php');
$blood = new Blood();

$number1 = parseGetData('number1', '0');
$number2 = parseGetData('number2', '0');
$number3 = parseGetData('number3', '0');
$total = parseGetData('total', '0');

$blood->update_blood_sample(array(
  'number1' => -1 * $number1,
  'number2' => -1 * $number2,
  'number3' => -1 * $number3
));
$sql = 'update `pet_config` set config_value = ' . $total . ' where config_name = "' . $blood->table . '_blood_number"';

$blood->db->query($sql);

$result['status'] = 1;
$result['number'] = $blood->check_last_blood();
$result['total'] = $blood->check_blood_sample();
