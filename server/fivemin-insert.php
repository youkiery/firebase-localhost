<?php 

require_once(ROOTDIR .'/fivemin.php');
$fivemin = new Fivemin();

$filter = array(
  'time' => parseGetData('time', 0),
  'page' => parseGetData('page', 1)
);

$data = array(
  'chamsoc' => parseGetData('chamsoc', 0),
  'tugiac' => parseGetData('tugiac', 0),
  'giaiphap' => parseGetData('giaiphap', 0),
  'ketqua' => parseGetData('ketqua', 0),
  'uytin' => parseGetData('uytin', 0),
  'dongdoi' => parseGetData('dongdoi', 0),
  'trachnhiem' => parseGetData('trachnhiem', 0),
  'tinhyeu' => parseGetData('tinhyeu', 0),
);

$result['status'] = 1;
$result['data'] = $fivemin->insert($data);
