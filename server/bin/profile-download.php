<?php
$zip = new ZipArchive;
define('ROOTDIR2', str_replace('/server', '', pathinfo(str_replace(DIRECTORY_SEPARATOR, '/', __file__), PATHINFO_DIRNAME)));
$id = parseGetData('id', '');

$fileToModify = 'word/document.xml';
$wordDoc = ROOTDIR2. "/template.docx";
$name = "analysis-". time() .".docx";
$exportDoc = ROOTDIR2. "/export/". $name;

copy($wordDoc, $exportDoc);
if ($zip->open($exportDoc) === TRUE) {
  $sql = 'select * from pet_test_profile where id = '. $id;
  $query = $mysqli->query($sql);
  $data = $query->fetch_assoc();
  
  $sql = 'select a.value, b.name, b.unit, b.flag, b.up, b.down from pet_test_profile_data a inner join pet_test_target b on a.pid = '. $id .' and a.tid = b.id';
  $query = $mysqli->query($sql);
  $data['target'] = array();
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
      $tar = $row['name'] .' giảm: '. $row['down'];
    }
    else if ($value > $e) {
      $tick = '^'; 
      $tar = $row['name'] .' tăng: '. $row['up'];
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
  
  $oldContents = $zip->getFromName($fileToModify);

  $newContents = str_replace('{customer}', $data['customer'], $oldContents);
  $newContents = str_replace('{address}', $data['address'], $newContents);
  $newContents = str_replace('{name}', $data['name'], $newContents);
  $newContents = str_replace('{weight}', $data['weight'], $newContents);
  $newContents = str_replace('{age}', $data['age'], $newContents);
  $newContents = str_replace('{gender}', ($data['gender'] ? 'Đực' : 'Cái'), $newContents);
  $newContents = str_replace('{type}', $data['type'], $newContents);
  $newContents = str_replace('{sampleid}', $data['id'], $newContents);
  $newContents = str_replace('{serial}', $data['serial'], $newContents);
  $newContents = str_replace('{sampletype}', $data['sampletype'], $newContents);
  $newContents = str_replace('{samplenumber}', $data['samplenumber'], $newContents);
  $newContents = str_replace('{samplesymbol}', $data['samplesymbol'], $newContents);
  $newContents = str_replace('{samplestatus}', ($data['samplestatus'] ? 'Đạt yêu cầu' : 'Không đạt yêu cầu'), $newContents);
  $newContents = str_replace('{doctor}', $data['doctor'], $newContents);
  $newContents = str_replace('{time}', date('d/m/Y', $data['time']), $newContents);
  $newContents = str_replace('{DD}', date('d', $data['time']), $newContents);
  $newContents = str_replace('{MM}', date('m', $data['time']), $newContents);
  $newContents = str_replace('{YYYY}', date('Y', $data['time']), $newContents);

  for ($i = 1; $i <= 18; $i++) { 
    if (!empty($data['target'][$i - 1])) {
      $profile = $data['target'][$i - 1];
      $newContents = str_replace('{target'. $i .'}', $profile['name'] ,$newContents);
      $newContents = str_replace('{unit'. $i .'}', $profile['unit'], $newContents);
      $newContents = str_replace('{flag'. $i .'}', $profile['tick'], $newContents);
      $newContents = str_replace('{range'. $i .'}', $profile['flag'], $newContents);
      $newContents = str_replace('{restar'. $i .'}', $profile['tar'], $newContents);

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
      $newContents = str_replace('{restar'. $i .'}', '', $newContents);
    }
  }  

  $zip->deleteName($fileToModify);
  $zip->addFromString($fileToModify, $newContents);
  $return = $zip->close();
  If ($return==TRUE){
    $result['status'] = 1;
    $result['link'] = 'http://'. $_SERVER['HTTP_HOST']. '/export/'. $name;
  }
} else {
  $result['messenger'] = 'Không thể xuất file';
}
