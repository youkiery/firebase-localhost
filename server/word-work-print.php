<?php
require_once 'bootstrap.php';
require_once(ROOTDIR .'/work.php');
$work = new Work();

$time = parseGetData('time');
if (empty($time)) $time = time();
else $time = totime($time);
$day = date('N', $time);
$startdate = ($day == '1' ? strtotime(date('Y/m/d', $time)) : strtotime('last monday', $time));
$enddate = $startdate + 60 * 60 * 24 * 7 - 1;
// echo date('d/m/Y', $startdate);die();

$filter = array(
  'startdate' => date('d/m/Y', $startdate),
  'enddate' => date('d/m/Y', $enddate),
  'keyword' => '',
  'userid' => '',
  'page' => parseGetData('page', 1),
  'status' => parseGetData('status', 0)
);
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\Style\TablePosition;
// Creating the new document...

$phpWord = new \PhpOffice\PhpWord\PhpWord();

$section= $phpWord->addSection(array('orientation' => 'landscape', 'marginLeft' => 600, 'marginRight' => 600,
'marginTop' => 600, 'marginBottom' => 600));
$header = array('size' => 16, 'bold' => true);

$fancyTableStyleName = 'Fancy Table';
$fancyTableStyle = array('borderSize' => 1, 'borderColor' => '000000', 'cellMargin' => 80, 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER, 'cellMargin' => 80);
$fancyTableFirstRowStyle = array();
$fancyTableCellStyle = array('valign' => 'center');
$fancyTableCellBtlrStyle = array('valign' => 'center', 'textDirection' => \PhpOffice\PhpWord\Style\Cell::TEXT_DIR_BTLR);
$fancyTableFontStyle = array('bold' => true);

$cellRowSpan = array('vMerge' => 'restart');
$cellRowContinue = array('vMerge' => 'continue');
$cellColSpan = array('gridSpan' => 2);

$phpWord->addTableStyle($fancyTableStyleName, $fancyTableStyle, $fancyTableFirstRowStyle);
$table = $section->addTable($fancyTableStyleName, array('tableWidth', array(100, 'pct')));
$header_style = array('bold' => true);
$header_option = array('align' => 'center');

$table->addRow();
$table->addCell(2000, $fancyTableCellStyle)->addText("Nhân viên", $header_style, $header_option);
$table->addCell(2000, $fancyTableCellStyle)->addText("Thứ 2", $header_style, $header_option);
$table->addCell(2000, $fancyTableCellStyle)->addText("Thứ 3", $header_style, $header_option);
$table->addCell(2000, $fancyTableCellStyle)->addText("Thứ 4", $header_style, $header_option);
$table->addCell(2000, $fancyTableCellStyle)->addText("Thứ 5", $header_style, $header_option);
$table->addCell(2000, $fancyTableCellStyle)->addText("Thứ 6", $header_style, $header_option);
$table->addCell(2000, $fancyTableCellStyle)->addText("Thứ 7", $header_style, $header_option);
$table->addCell(2000, $fancyTableCellStyle)->addText("Chủ nhật", $header_style, $header_option);
$table->addCell(2000, $fancyTableCellStyle)->addText("Tuần trước", $header_style, $header_option);

$list = $work->getWork($filter); // get all
$overdate = array();
$sql = 'select id, userid, cometime, calltime, process, content, note, image from `'. $work->prefix .'` where active = 1 and process < 100 and calltime < '. $startdate;
$query = $work->db->query($sql);
while ($row = $query->fetch_assoc()) {
  if (empty($user[$row['userid']])) {
    $userinfo = checkUserId($row['userid']);
    $user[$row['userid']] = (!empty($userinfo['last_name']) ? $userinfo['last_name'] . ' ': '') . $userinfo['first_name'];
  }
  $row['name'] = $user[$row['userid']];
  $row['color'] = ($row['calltime'] < $time ? 'red' : '');
  $row['day'] = date('N', $row['calltime']);
  $row['cometime'] = date('d/m/Y', $row['cometime']);
  $row['calltime'] = date('d/m/Y', $row['calltime']);
  $row['overtime'] = true;
  $row['image'] = explode(',', $row['image']);
  $overdate []= $row;
}

$data = array();
foreach ($list as $row) {
  if (empty($data[$row['userid']])) $data[$row['userid']] = array('name' => $row['name'], 'data' => array(1 => array(), array(), array(), array(), array(), array(), array(), array()));
  $data[$row['userid']]['data'][$row['day']] []= $row;
}

foreach ($overdate as $row) {
  if (empty($data[$row['userid']])) $data[$row['userid']] = array('name' => $row['name'], 'data' => array(1 => array(), array(), array(), array(), array(), array(), array(), array()));
  $data[$row['userid']]['data'][8] []= $row;
}

// echo json_encode($data);die();

foreach ($data as $user) {
  $table->addRow();
  $table->addCell(2000, $fancyTableCellStyle)->addText($user['name']);
  // echo json_encode($user);die();
  foreach ($user['data'] as $day) {
    $index = 1;
    $firstLine = true;
    $cell = $table->addCell(2000, $fancyTableCellStyle);
    // if (count($day)) {var_dump($day);die();}
    foreach ($day as $row) {
      // if ($firstLine) $firstLine = false;
      // else $cell->addTextBreak();
      // kiểm tra quá hạn chưa
      $xtra = '';
      if ($row['process'] == 100) $xtra = ' (R)';
      else if (!empty($row['overtime'])) $xtra = ' (QH)';
      $cell->addText(($index ++) . '. ' .$row['content'] . $xtra);
    }
  }
}

// $table->addRow();
// $table->addCell(null, $cellRowContinue);
// $table->addCell(null, $cellRowContinue);
// $table->addCell(2000)->addText("4");
// $table->addCell(2000)->addText("5");
// $table->addCell(null, $cellRowContinue);

// $table->addRow();
// $table->addCell(2000);
// $table->addCell(2000);
// $table->addCell(2000);
// $table->addCell(2000);
// $table->addCell(2000);

$name = 'work-'. time();
$doc = '/files/'. $name .'.docx';
$preview = '/files/'. $name .'.html';

$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'HTML');
$objWriter->save(ROOTDIR . $preview);

$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
$objWriter->save(ROOTDIR . $doc);

// $objWriter->save(ROOTDIR . $preview);
// $fp = fopen(ROOTDIR . $preview,"wb");
// fwrite($fp,'');
// $phpWord->save(ROOTDIR . $preview, 'HTML');

// die('d');

$result['status'] = 1;
$result['name'] = $name;
$result['doc'] = 'http://' . $_SERVER['SERVER_NAME'] . '/ionic/'. $branch .'/'. $doc;
$result['preview'] = 'http://' . $_SERVER['SERVER_NAME'] . '/ionic/'. $branch .'/'. $preview;
