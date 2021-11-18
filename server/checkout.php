<?php
$dir = str_replace('/server', '', ROOTDIR);
include $dir .'/PHPExcel/IOFactory.php';

$x = array();
$xr = array(0 => 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ', 'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'HI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO');
foreach ($xr as $key => $value) {
  $x[$value] = $key;
}

function excel() {
  global $data, $db, $result, $dir, $x, $xr, $_FILES;

  // $des = $dir ."export/DanhSachChiTietHoaDon_KV09102021-222822-523-1633793524.xlsx";

  $name1 = pathinfo($_FILES['file']['name'], PATHINFO_FILENAME) ."-". time() .".". pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
  $name2 = pathinfo($_FILES['file2']['name'], PATHINFO_FILENAME) ."-". time() .".". pathinfo($_FILES['file2']['name'], PATHINFO_EXTENSION);
  $kiottemp = getData($_FILES['file'], $name1);
  $vietcomtemp = getData($_FILES['file2'], $name2);

  // kiểm tra thời gian
  $res = array(
    'on' => 1,
    'pair' => array(),
    'kiot' => array(),
    'vietcom' => array()
  );

  $kiot = array();
  for($i = 2; $i < count($kiottemp[1]); $i ++) {
    if ($kiottemp[7][$i]) $kiot []= $kiottemp[7][$i];
  }

  $vietcom = array();
  for($i = 14; $i < count($vietcomtemp[1]); $i ++) {
    if ($vietcomtemp[2][$i] == '+') {
      $money = str_replace(',', '', $vietcomtemp[3][$i]);
      $check = false;
      foreach ($kiot as $key => $value) {
        if ($value == $money) {
          $check = true;
          unset($kiot[$key]);
          $res['pair'] []= array('money' => $money, 'info' => $vietcomtemp[4][$i]);
          break;
        }
      }
      if (!$check) $res['vietcom'] []= array('money' => $money, 'info' => $vietcomtemp[4][$i]);
    }
    else break;
  }

  foreach ($kiot as $money) {
    $res['kiot'] []= $money;
  }

  if (file_exists("$dir/export/$name1")) {
    unlink("$dir/export/$name1");
  }
  if (file_exists("$dir/export/$name2")) {
    unlink("$dir/export/$name2");
  }

  $result['data'] = $res;
  $result['messenger'] = 'Đã tải file Excel lên';
  $result['status'] = 1;
  return $result;
}

function getData($file, $tar) {
  global $x, $xr, $dir;
  $raw = $file['tmp_name'];
  $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
  $name = pathinfo($file['name'], PATHINFO_FILENAME);
  $des = "$dir/export/$tar";

  move_uploaded_file($raw, $des);

  $inputFileType = PHPExcel_IOFactory::identify($des);
  $objReader = PHPExcel_IOFactory::createReader($inputFileType);
  $objReader->setReadDataOnly(true);
  $objPHPExcel = $objReader->load($des);
  
  $sheet = $objPHPExcel->getSheet(0); 

  $highestRow = $sheet->getHighestRow(); 
  $highestColumn = $sheet->getHighestColumn();

  $excel = array();
  for ($j = 0; $j <= $x[$highestColumn]; $j ++) {
    $excel[$j] = array();
    for ($i = 1; $i < $highestRow; $i++) { 
      $excel[$j][$i] = $sheet->getCell($xr[$j] . $i)->getValue();
    }
  }

  return $excel;
}
