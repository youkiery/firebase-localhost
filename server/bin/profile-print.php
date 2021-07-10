<?php 

$id = parseGetData('id', '');

$sql = 'select * from pet_test_profile where id = '. $id;
$query = $mysqli->query($sql);
$data = $query->fetch_assoc();
$sql = 'select a.value, b.name, b.unit, b.flag, b.up, b.down from pet_test_profile_data a inner join pet_test_target b on a.pid = '. $id .' and a.tid = b.id';
$query = $mysqli->query($sql);
$data['target'] = array();
$i = 1;
while ($row = $query->fetch_assoc()) {
  $flag = explode(' - ', $row['flag']);
  $value = floatval($row['value']);
  if (count($flag) == 2) {
    $s = floatval($flag[0]);
    $e = floatval($flag[1]);
  }
  else {
    $s = 0; $e = 1;
  }
  $tick = '';
  $tar = '';
  if ($value < $s) {
    $tick = 'v';
    $tar = '<b>'. $i . '. '. $row['name'] .' giảm:</b> '. $row['down'];
    $i ++;
  }
  else if ($value > $e) {
    $tick = '^'; 
    $tar = '<b>'. $i . '. '. $row['name'] .' tăng:</b> '. $row['up'];
    $i ++;
  }

  $data['target'] []= array(
    'name' => $row['name'],
    'value' => $row['value'],
    'unit' => $row['unit'],
    'flag' => $row['flag'],
    'tar' => $tar,
    'tick' => $tick
  );
}

$sql = 'select value from pet_test_configv2 where name = "type" limit 1 offset '. $data['type'];
$query = $mysqli->query($sql);
$row = $query->fetch_assoc();
$data['type'] = $row['value'];

$sql = 'select value from pet_test_configv2 where name = "sampletype" limit 1 offset '. $data['sampletype'];
$query = $mysqli->query($sql);
$row = $query->fetch_assoc();
$data['sampletype'] = $row['value'];

$sql = 'select * from pet_users where userid = '. $data['doctor'];
$query = $mysqli->query($sql);
$doctor = $query->fetch_assoc();

$data['doctor'] = $doctor['last_name'] . ' '. $doctor['first_name'];

define('ROOTDIR2', str_replace('/server', '', pathinfo(str_replace(DIRECTORY_SEPARATOR, '/', __file__), PATHINFO_DIRNAME)));
$html = file_get_contents ( ROOTDIR2. '/template.php');

$html = str_replace('{customer}', $data['customer'], $html);
$html = str_replace('{address}', $data['address'], $html);
$html = str_replace('{name}', $data['name'], $html);
$html = str_replace('{weight}', $data['weight'], $html);
$html = str_replace('{age}', $data['age'], $html);
$html = str_replace('{gender}', ($data['gender'] ? 'Đực' : 'Cái'), $html);
$html = str_replace('{type}', $data['type'], $html);
$html = str_replace('{sampleid}', $data['id'], $html);
$html = str_replace('{serial}', $data['serial'], $html);
$html = str_replace('{sampletype}', $data['sampletype'], $html);
$html = str_replace('{samplenumber}', $data['samplenumber'], $html);
$html = str_replace('{samplesymbol}', $data['samplesymbol'], $html);
$html = str_replace('{samplestatus}', ($data['samplestatus'] ? 'Đạt yêu cầu' : 'Không đạt yêu cầu'), $html);
$html = str_replace('{doctor}', $data['doctor'], $html);
$time = $data['time'] / 1000;
$html = str_replace('{time}', date('d/m/Y', $time), $html);
$html = str_replace('{DD}', date('d', $time), $html);
$html = str_replace('{MM}', date('m', $time), $html);
$html = str_replace('{YYYY}', date('Y', $time), $html);

for ($i = 1; $i <= 18; $i++) { 
  if (!empty($data['target'][$i - 1])) {
    $profile = $data['target'][$i - 1];
    $html = str_replace('{target'. $i .'}', $profile['name'] ,$html);
    $html = str_replace('{unit'. $i .'}', $profile['unit'], $html);
    $html = str_replace('{flag'. $i .'}', $profile['tick'], $html);
    $html = str_replace('{range'. $i .'}', $profile['flag'], $html);
    $html = str_replace('{restar'. $i .'}', $profile['tar'], $html);

    if (!empty($profile['tick'])) {
      $html = str_replace('{ret'. $i .'}', $profile['value'], $html);
      $html = str_replace('{res'. $i .'}', '', $html);
    }
    else {
      $html = str_replace('{res'. $i .'}', $profile['value'], $html);
      $html = str_replace('{ret'. $i .'}', '', $html);
    }
  }
  else {
    $html = str_replace('{target'. $i .'}', '', $html);
    $html = str_replace('{res'. $i .'}', '', $html);
    $html = str_replace('{ret'. $i .'}', '', $html);
    $html = str_replace('{unit'. $i .'}', '', $html);
    $html = str_replace('{flag'. $i .'}', '', $html);
    $html = str_replace('{range'. $i .'}', '', $html);
    $html = str_replace('{restar'. $i .'}', '', $html);
  }
}  

$result['status'] = 1;
$result['html'] = $html;
