<?php
function download() {
  global $data, $db, $result;
    
  $zip = new ZipArchive;
  
  $fileToModify = 'word/document.xml';
  $wordDoc = DIR. "/include/export/template.docx";
  $name = "analysis-". time() .".docx";
  $exportDoc = DIR. "/include/export/". $name;
  
  copy($wordDoc, $exportDoc);
  if ($zip->open($exportDoc) === TRUE) {
    $sql = "select * from pet_test_profile where id = $data->id";
    $query = $db->query($sql);
    $prof = $query->fetch_assoc();
    
    $sql = "select a.value, b.name, b.unit, b.flag, b.up, b.down from pet_test_profile_data a inner join pet_test_target b on a.pid = $data->id and a.tid = b.id and b.module = 'profile'";
    $query = $db->query($sql);
    $prof['target'] = array();
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
        $tick = '<';
        $tar = $row['name'] .' giảm: '. $row['down'];
      }
      else if ($value > $e) {
        $tick = '>'; 
        $tar = $row['name'] .' tăng: '. $row['up'];
      }
      
      $prof['target'] []= array(
        'name' => $row['name'],
        'value' => $row['value'],
        'unit' => $row['unit'],
        'flag' => $row['flag'],
        'tar' => $tar,
        'tick' => $tick
      );
    }
    
    $sql = 'select value from pet_test_config where name = "species" limit 1';
    $row = $db->fetch($sql);
    $prof['species'] = $row['value'];
    
    $sql = 'select value from pet_test_config where name = "sampletype" limit 1';
    $row = $db->fetch($sql);
    $prof['sampletype'] = $row['value'];
    
    $sql = 'select * from pet_users where userid = '. $prof['doctor'];
    $doctor = $db->fetch($sql);
    
    $prof['doctor'] = $doctor['fullname'];
    
    $oldContents = $zip->getFromName($fileToModify);
  
    $newContents = str_replace('{customer}', $prof['customer'], $oldContents);
    $newContents = str_replace('{address}', $prof['address'], $newContents);
    $newContents = str_replace('{name}', $prof['name'], $newContents);
    $newContents = str_replace('{weight}', $prof['weight'], $newContents);
    $newContents = str_replace('{age}', $prof['age'], $newContents);
    $newContents = str_replace('{gender}', ($prof['gender'] == 0 ? 'Đực' : 'Cái'), $newContents);
    $newContents = str_replace('{type}', $prof['species'], $newContents);
    $newContents = str_replace('{sampleid}', $prof['id'], $newContents);
    $newContents = str_replace('{serial}', $prof['serial'], $newContents);
    $newContents = str_replace('{sampletype}', $prof['sampletype'], $newContents);
    $newContents = str_replace('{samplenumber}', $prof['samplenumber'], $newContents);
    $newContents = str_replace('{samplesymbol}', $prof['samplesymbol'], $newContents);
    $newContents = str_replace('{samplestatus}', ($prof['samplestatus'] ? 'Đạt yêu cầu' : 'Không đạt yêu cầu'), $newContents);
    $newContents = str_replace('{doctor}', $prof['doctor'], $newContents);
    $newContents = str_replace('{time}', date('d/m/Y', $prof['time']), $newContents);
    $newContents = str_replace('{DD}', date('d', $prof['time']), $newContents);
    $newContents = str_replace('{MM}', date('m', $prof['time']), $newContents);
    $newContents = str_replace('{YYYY}', date('Y', $prof['time']), $newContents);
  
    for ($i = 1; $i <= 18; $i++) { 
      if (!empty($prof['target'][$i - 1])) {
        $profile = $prof['target'][$i - 1];
        $newContents = str_replace('{target'. $i .'}', $profile['name'] ,$newContents);
        $newContents = str_replace('{unit'. $i .'}', $profile['unit'], $newContents);
        $newContents = str_replace('{range'. $i .'}', $profile['flag'], $newContents);
        $newContents = str_replace('{restar'. $i .'}', $profile['tar'], $newContents);
  
        if ($profile['tick'] == '<') {
          $newContents = str_replace('{rg'. $i .'}', $profile['value'], $newContents);
          $newContents = str_replace('{rn'. $i .'}', '', $newContents);
          $newContents = str_replace('{rt'. $i .'}', '', $newContents);
        }
        else if ($profile['tick'] == '>') {
          $newContents = str_replace('{rt'. $i .'}', $profile['value'], $newContents);
          $newContents = str_replace('{rn'. $i .'}', '', $newContents);
          $newContents = str_replace('{rg'. $i .'}', '', $newContents);
        }
        else {
          $newContents = str_replace('{rn'. $i .'}', $profile['value'], $newContents);
          $newContents = str_replace('{rt'. $i .'}', '', $newContents);
          $newContents = str_replace('{rg'. $i .'}', '', $newContents);
        }
      }
      else {
        $newContents = str_replace('{target'. $i .'}', '', $newContents);
        $newContents = str_replace("{rn$i}", '', $newContents);
        $newContents = str_replace("{rt$i}", '', $newContents);
        $newContents = str_replace("{rg$i}", '', $newContents);
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
      $result['link'] = 'http://'. $_SERVER['HTTP_HOST']. '/include/export/'. $name;
    }
  } else {
    $result['messenger'] = 'Không thể xuất file';
  }

  return $result;
}

function get() {
  global $data, $db, $result;
    
  $sql = 'select * from pet_test_profile where id = '. $id;
  $query = $db->query($sql);
  $data = $query->fetch_assoc();
  $sql = "select a.value, b.name, b.unit, b.flag, b.up, b.down from pet_test_profile_data a inner join pet_test_target b on a.pid = $id and a.tid = b.id and b.module = 'profile'";
  $query = $db->query($sql);
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
  
  $sql = 'select value from pet_test_config where name = "type" limit 1 offset '. $data['type'];
  $query = $db->query($sql);
  $row = $query->fetch_assoc();
  $data['type'] = $row['value'];
  
  $sql = 'select value from pet_test_config where name = "sampletype" limit 1 offset '. $data['sampletype'];
  $query = $db->query($sql);
  $row = $query->fetch_assoc();
  $data['sampletype'] = $row['value'];
  
  $sql = 'select * from pet_users where userid = '. $data['doctor'];
  $query = $db->query($sql);
  $doctor = $query->fetch_assoc();
  
  $data['doctor'] = $doctor['fullname'];
  
  $result['status'] = 1;
  $result['data'] = $data;

  return $result;
}

function init() {
  global $data, $db, $result;
    
  $sql = "select * from pet_test_config where module = 'profile' and name = 'serial' limit 1";
  if (empty($serial = $db->fetch($sql))) $serial = 0;
  $serial = intval($serial) + 1;

  $result['status'] = 1;
  $result['list'] = getlist();
  $result['serial'] = $serial;
  $result['type'] = typelist();
  $result['species'] = specieslist();
  $result['target'] = targetlist();

  return $result;
}

function auto() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['list'] = getlist();
  return $result;
}

function updateprofile() {
  global $data, $db, $result;

  $userid = checkuserid();

  $time = time();
  $sql = "update pet_test_profile set customer = '$data->name', phone = '$data->phone', address = '$data->address', name = '$data->petname', weight = '$data->weight', age = '$data->age', gender = $data->gender, species = '$data->species', serial = '$data->serial', sampletype = '$data->sampletype', samplenumber = '$data->samplenumber', samplesymbol = '$data->samplesymbol', samplestatus = '$data->samplestatus', symptom = '$data->symptom' where id = $data->id";
  $db->query($sql);

  foreach ($data->target as $tid => $target) {
    $sql = "select * from pet_test_profile_data where pid = $data->id and tid = $tid";
    if (empty($d = $db->fetch($sql))) $sql = "insert into pet_test_profile_data (pid, tid, value) values ($data->id, $tid, '$target')";
    else $sql = "update pet_test_profile_data set value = $target where id = $d[id]";
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['list'] = getlist();

  return $result;
}

function insert() {
  global $data, $db, $result;

  $sql = "select * from pet_test_target where active = 1 and module = 'profile' order by id asc";
  $query = $db->query($sql);
  $list = $db->all($sql);
  $userid = checkuserid();

  $time = time();
  $sql = "insert into pet_test_profile (customer, phone, address, name, weight, age, gender, species, serial, sampletype, samplenumber, samplesymbol, samplestatus, symptom, doctor, time) values ('$data->name', '$data->phone', '$data->address', '$data->petname', '$data->weight', '$data->age', '$data->gender', $data->species, '$data->serial', $data->sampletype, '$data->samplenumber', '$data->samplesymbol', '$data->samplestatus', '$data->symptom', $userid, $time)";
  $id = $db->insertid($sql);
  // $id = 18;

  foreach ($list as $target) {
    $sql = "insert into pet_test_profile_data (pid, tid, value) values ($id, $target[id], '". $data->target->{$target['id']} ."')";
    $db->query($sql);
  }

  $serial = floatval($data->serial) + 1;
  $sql = 'select * from pet_test_config where name = "serial"';
  $query = $db->query($sql);
  $config = $query->fetch_assoc();
  if (empty($config)) $sql = "insert into pet_test_config (module, name, value) values('profile', 'serial', '$serial')";
  else $sql = "update pet_test_config set value = '$serial' where module = 'profile' and name = 'serial'";
  $db->query($sql);

  $data = array(
    'id' => $id,
    'customer' => $data->name,
    'phone' => $data->phone,
    'time' => date('d/m/Y', $time)
  );

  $result['status'] = 1;
  $result['data'] = $data;
  $result['serial'] = $serial;

  return $result;
}

function printword() {
  global $data, $db, $result;

  $sql = "select * from pet_test_profile where id = $data->id";
  $prof = $db->fetch($sql);

  $sql = "select a.value, b.name, b.unit, b.flag, b.up, b.down from pet_test_profile_data a inner join pet_test_target b on a.pid = $data->id and a.tid = b.id and b.module = 'profile'";
  $l = $db->all($sql);
  $prof['target'] = array();
  $i = 1;
  foreach ($l as $row) {
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
      $tick = '<';
      $tar = '<b>'. $row['name'] .' giảm:</b> '. $row['down'];
    }
    else if ($value > $e) {
      $tick = '>'; 
      $tar = '<b>'. $row['name'] .' tăng:</b> '. $row['up'];
      $i ++;
    }

    $prof['target'] []= array(
      'name' => $row['name'],
      'value' => $row['value'],
      'unit' => $row['unit'],
      'flag' => $row['flag'],
      'tar' => $tar,
      'tick' => $tick
    );
  }

  $sql = 'select value from pet_test_config where name = "species" limit 1';
  $row = $db->fetch($sql);
  $prof['species'] = $row['value'];

  $sql = 'select value from pet_test_config where name = "sampletype" limit 1';
  $row = $db->fetch($sql);
  $prof['sampletype'] = $row['value'];

  $sql = 'select * from pet_users where userid = '. $prof['doctor'];
  $doctor = $db->fetch($sql);

  $prof['doctor'] = $doctor['fullname'];

  $html = file_get_contents ( DIR. '/include/export/template.php');

  $html = str_replace('{customer}', $prof['customer'], $html);
  $html = str_replace('{address}', $prof['address'], $html);
  $html = str_replace('{name}', $prof['name'], $html);
  $html = str_replace('{weight}', $prof['weight'], $html);
  $html = str_replace('{age}', $prof['age'], $html);
  $html = str_replace('{gender}', ($prof['gender'] == 0 ? 'Đực' : 'Cái'), $html);
  $html = str_replace('{type}', $prof['species'], $html);
  $html = str_replace('{sampleid}', $prof['id'], $html);
  $html = str_replace('{serial}', $prof['serial'], $html);
  $html = str_replace('{sampletype}', $prof['sampletype'], $html);
  $html = str_replace('{samplenumber}', $prof['samplenumber'], $html);
  $html = str_replace('{samplesymbol}', $prof['samplesymbol'], $html);
  $html = str_replace('{samplestatus}', ($prof['samplestatus'] ? 'Đạt yêu cầu' : 'Không đạt yêu cầu'), $html);
  $html = str_replace('{doctor}', $prof['doctor'], $html);
  $html = str_replace('{time}', date('d/m/Y', $prof['time']), $html);
  $html = str_replace('{DD}', date('d', $prof['time']), $html);
  $html = str_replace('{MM}', date('m', $prof['time']), $html);
  $html = str_replace('{YYYY}', date('Y', $prof['time']), $html);

  for ($i = 1; $i <= 18; $i++) { 
    if (!empty($prof['target'][$i - 1])) {
      $profile = $prof['target'][$i - 1];
      $html = str_replace('{target'. $i .'}', $profile['name'] ,$html);
      $html = str_replace('{unit'. $i .'}', $profile['unit'], $html);
      $html = str_replace('{range'. $i .'}', $profile['flag'], $html);
      $html = str_replace('{restar'. $i .'}', $profile['tar'], $html);

      if ($profile['tick'] == '<') {
        $html = str_replace('{rg'. $i .'}', $profile['value'], $html);
        $html = str_replace('{rn'. $i .'}', '', $html);
        $html = str_replace('{rt'. $i .'}', '', $html);
      }
      else if ($profile['tick'] == '>') {
        $html = str_replace('{rt'. $i .'}', $profile['value'], $html);
        $html = str_replace('{rn'. $i .'}', '', $html);
        $html = str_replace('{rg'. $i .'}', '', $html);
      }
      else {
        $html = str_replace('{rn'. $i .'}', $profile['value'], $html);
        $html = str_replace('{rt'. $i .'}', '', $html);
        $html = str_replace('{rg'. $i .'}', '', $html);
      }
    }
    else {
      $html = str_replace('{target'. $i .'}', '', $html);
      $html = str_replace('{rn'. $i .'}', '', $html);
      $html = str_replace('{rt'. $i .'}', '', $html);
      $html = str_replace('{rg'. $i .'}', '', $html);
      $html = str_replace('{unit'. $i .'}', '', $html);
      $html = str_replace('{range'. $i .'}', '', $html);
      $html = str_replace('{restar'. $i .'}', '', $html);
    }
  }  

  $result['status'] = 1;
  $result['html'] = $html;

  return $result;
}

function remove() {
  global $data, $db, $result;

  $sql = 'delete from pet_test_profile where id = '. $id;
  $query = $db->query($sql);
  $sql = 'delete from pet_test_profile_data where pid = '. $id;
  $query = $db->query($sql);

  $sql = 'select id, name, customer, time from pet_test_profile where name like "%'. $filter['keyword'] .'%" or customer like "%'. $filter['keyword'] .'%" order by id desc limit '. $filter['page'] * 10;
  $query = $db->query($sql);
  $list = array();

  while ($row = $query->fetch_assoc()) {
    $list []= $row;
  }

  $result['status'] = 1;
  $result['list'] = getlist();

  return $result;
}

function insertselect() {
  global $data, $db, $result;

  $sql = "select * from pet_test_config where module = 'profile' and name = '$data->typename' and value = '$data->typevalue'";
  if (empty($c = $db->fetch($sql))) {
    $sql = "insert into pet_test_config (module, name, value) values('profile', '$data->typename', '$data->typevalue')";
    $db->query($sql);
  }

  $result['status'] = 1;
  if ($data->typename == 'sampletype') $result['list'] = typelist();
  else $result['list'] = specieslist();
  return $result;
}

// function suggestinsert() {
//   global $data, $db, $result;
    
//   $sql = "select * from pet_test_config where name = '$type' and value = '$name'";
//   $query = $db->query($sql);
//   $data = $query->fetch_assoc();
  
//   if (!empty($data)) {
//     $result['messenger'] = 'Đã tồn tại';
//   }
//   else {
//     $sql = "insert into pet_test_config (name, value) values ('$type', '$name')";
//     $query = $db->query($sql);
    
//     $list = array();
//     $sql = 'select * from pet_test_config where name = "'. $type .'" order by id asc';
//     $query = $db->query($sql);
//     $index = 0;
//     while ($row = $query->fetch_assoc()) {
//       $list []= array(
//         'id' => $index ++,
//         'name' => $row['value']
//       );
//     }
//     $result['list'] = $list;
//     $result['status'] = 1;
//   }

//   return $result;
// }

function checkcustomer() {
  global $db, $data;
  $sql = 'select * from pet_test_customer where phone = "'. $data->phone .'"';
  $c = $db->fetch($sql);

  if (empty($c)) {
    $sql = "insert into pet_test_customer (name, phone, address) values('$data->name', '$data->phone', '$data->address')";
    $c['id'] = $db->insertid($sql);
  }
  else {
    $sql = "update pet_test_customer set name = '$data->name', address = '$data->address' where phone = '$data->phone'";
    $db->query($sql);
  }
  return $c['id'];
}

function getlist() {
  global $db, $data;

  $sql = "select a.*, c.name as doctor from pet_test_profile a inner join pet_users c on a.doctor = c.userid where a.phone like '%$data->key%' or a.customer like '%$data->key%' order by id desc limit 10 offset 0";
  $query = $db->query($sql);
  $list = array();
  
  while ($row = $query->fetch_assoc()) {
    $sql = "select tid, value from pet_test_profile_data where pid = $row[id]";
    $row['target'] = $db->obj($sql, 'tid', 'value');
    $row['time'] = date('d/m/Y', $row['time']);
    $list []= $row;
  }
  return $list;
}

function typelist() {
  global $db;

  $sql = "select id, value as name from pet_test_config where module = 'profile' and name = 'sampletype' order by value asc";
  return $db->all($sql);
}

function specieslist() {
  global $db;

  $sql = "select id, value as name from pet_test_config where module = 'profile' and name = 'species' order by value asc";
  return $db->all($sql);
}

function targetlist() {
  global $db;

  $sql = "select * from pet_test_target where active = 1 and module = 'profile' order by id asc";

  return $db->all($sql);
}
