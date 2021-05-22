<?php
$zip = new ZipArchive;
define('ROOTDIR', pathinfo(str_replace(DIRECTORY_SEPARATOR, '/', __file__), PATHINFO_DIRNAME));

$fileToModify = 'word/document.xml';
$wordDoc = "template.docx";
$exportDoc = ROOTDIR. "/export/analysis-". time() .".docx";

include_once('./config.php');
$servername = $config['servername'];
$username = $config['username'];
$password = $config['password'];
$database = $config['database'];
$db = new mysqli($servername, $username, $password, $database);
if ($db->connect_errno) die('error: '. $db -> connect_error);
$db->set_charset('utf8');

copy($wordDoc, $exportDoc);
if ($zip->open($exportDoc) === TRUE) {
  $id = 7;
  $sql = 'select * from pet_test_profile where id = '. $id;
  $query = $db->query($sql);
  $data = $query->fetch_assoc();

  $sql = 'select a.value, b.name, b.unit, b.flag from pet_test_profile_data a inner join pet_test_target b on a.pid = '. $id .' and a.tid = b.id';
  $query = $db->query($sql);
  $data['target'] = array();
  while ($row = $query->fetch_assoc()) {
    $flag = explode(' - ', $row['flag']);
    $value = floatval($row['value']);
    $s = floatval($flag[0]);
    $e = floatval($flag[1]);
    $tick = '';
    if ($value < $s) $tick = '^';
    else if ($value > $e) $tick = 'v'; 

    $data['target'] []= array(
      'name' => $row['name'],
      'value' => $row['value'],
      'unit' => $row['unit'],
      'flag' => $row['flag'],
      'tick' => $tick
    );
  }

  $sql = 'select value from pet_test_configv2 where name = "type" limit 1 offset '. $data['type'];
  $query = $db->query($sql);
  $row = $query->fetch_assoc();
  $data['type'] = $row['value'];

  $sql = 'select value from pet_test_configv2 where name = "sampletype" limit 1 offset '. $data['sampletype'];
  $query = $db->query($sql);
  $row = $query->fetch_assoc();
  $data['sampletype'] = $row['value'];

  $sql = 'select * from pet_users where userid = '. $data['doctor'];
  $query = $db->query($sql);
  $doctor = $query->fetch_assoc();

  $data['doctor'] = $doctor['last_name'] . ' '. $doctor['first_name'];

  $oldContents = $zip->getFromName($fileToModify);

  $newContents = str_replace('{customer}', $data['customer'], $oldContents);
  $newContents = str_replace('{address}', $data['address'], $newContents);
  $newContents = str_replace('{name}', $data['name'], $newContents);
  $newContents = str_replace('{weight}', $data['weight'], $newContents);
  $newContents = str_replace('{age}', $data['age'], $newContents);
  $newContents = str_replace('{gender}', ($data['gender'] ? 'Đực' : 'Cái'), $newContents);
  $newContents = str_replace('{type}', $data['type'], $newContents);
  $newContents = str_replace('{sampleid}', $data['sampleid'], $newContents);
  $newContents = str_replace('{serial}', $data['serial'], $newContents);
  $newContents = str_replace('{sampletype}', $data['sampletype'], $newContents);
  $newContents = str_replace('{samplenumber}', $data['samplenumber'], $newContents);
  $newContents = str_replace('{samplesymbol}', $data['samplesymbol'], $newContents);
  $newContents = str_replace('{samplestatus}', ($data['samplestatus'] ? 'Đạt yêu cầu' : 'Không đạt yêu cầu'), $newContents);
  $newContents = str_replace('{doctor}', $data['doctor'], $newContents);
  $newContents = str_replace('{time}', date('d/m/Y', $data['time']), $newContents);

  for ($i = 1; $i <= 18; $i++) { 
    if (!empty($data['target'][$i - 1])) {
      $profile = $data['target'][$i - 1];
      $newContents = str_replace('{target'. $i .'}', $profile['name'] ,$newContents);
      $newContents = str_replace('{unit'. $i .'}', $profile['unit'], $newContents);
      $newContents = str_replace('{flag'. $i .'}', $profile['tick'], $newContents);
      $newContents = str_replace('{range'. $i .'}', $profile['flag'], $newContents);

      if (!empty($profile['tick'])) {
        $newContents = str_replace('{ret'. $i .'}', $profile['value'], $newContents);
        $newContents = str_replace('{res'. $i .'}', '', $newContents);
      }
      else {
        $newContents = str_replace('{res'. $i .'}', $profile['value'], $newContents);
        $newContents = str_replace('{ret'. $i .'}', '', $newContents);
      }
    }
    else {
      $newContents = str_replace('{target'. $i .'}', '', $newContents);
      $newContents = str_replace('{res'. $i .'}', '', $newContents);
      $newContents = str_replace('{ret'. $i .'}', '', $newContents);
      $newContents = str_replace('{unit'. $i .'}', '', $newContents);
      $newContents = str_replace('{flag'. $i .'}', '', $newContents);
      $newContents = str_replace('{range'. $i .'}', '', $newContents);
    }
  }  

  $zip->deleteName($fileToModify);
  $zip->addFromString($fileToModify, $newContents);
  $return = $zip->close();
  If ($return==TRUE){
    echo "Success!";
  }
} else {
  echo 'failed';
}