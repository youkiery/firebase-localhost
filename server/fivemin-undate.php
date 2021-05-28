<?php 

require_once(ROOTDIR .'/fivemin.php');
$fivemin = new Fivemin();

$filter = array(
  'time' => parseGetData('time', 0)
);

$data = array(
  'id' => parseGetData('id', 0),
  'chamsoc' => parseGetData('chamsoc', 0),
  'tugiac' => parseGetData('tugiac', 0),
  'giaiphap' => parseGetData('giaiphap', 0),
  'uytin' => parseGetData('uytin', 0),
  'ketqua' => parseGetData('ketqua', 0),
  'dongdoi' => parseGetData('dongdoi', 0),
  'trachnhiem' => parseGetData('trachnhiem', 0),
  'tinhyeu' => parseGetData('tinhyeu', 0),
  'hoanthanh' => parseGetData('hoanthanh', 0)
);

$result['status'] = 1;
$fivemin->update($data);
$result['list'] = $fivemin->init($filter);
