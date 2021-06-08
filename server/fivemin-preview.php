<?php 

$id = parseGetData('id', '0');
$list = array(
  'muctieu' => array(
    'ten' => 'Mục tiêu doanh số',
    'danhsach' => array()
  ),
  'chamsoc' => array(
    'ten' => 'Chăm sóc khách hàng',
    'danhsach' => array()
  ),
  'tugiac' => array(
    'ten' => 'Tính tự giác',
    'danhsach' => array()
  ),
  'chuyenmin' => array(
    'ten' => 'Mục tiêu chuyên môn',
    'danhsach' => array()
  ),
  'dongdoi' => array(
    'ten' => 'Tính đồng đội',
    'danhsach' => array()
  ),
  'giaiphap' => array(
    'ten' => 'Ý tưởng và pháp pháp',
    'danhsach' => array()
  ),
);

$html = '';

$sql = 'select a.*, concat(last_name, " ", first_name) as fullname from pet_test_5min a inner join pet_users b on a.nhanvien = b.userid where id = '. $id;
$query = $mysqli->query($sql);
$data = $query->fetch_assoc();

$sql = 'select * from pet_test_5min_hang where idcha = '. $id;
$query = $mysqli->query($sql);
$index = 1;

while ($row = $query->fetch_assoc()) {
  if ($row['noidung'] !== 'undefined' && strlen($row['noidung'])) {
    $list[$row['tieuchi']]['danhsach'] []= $row;
  }
}

$html = '';
foreach ($list as $tieuchi => $dulieu) {
  if (count($dulieu['danhsach'])) {
    $html .= '
      <tr>
        <td colspan="3">
        <b> '. $dulieu['ten'] .' </b>
        </td>
      </tr>
    ';
    $index = 1;
    foreach ($dulieu['danhsach'] as $danhsach) {
      $html .= '
        <tr>
          <td> '. ($index ++) .' </td>
          <td> '. ($danhsach['noidung']) .' </td>
          <td> '. ($danhsach['hoanthanh'] > 0 ? 'HT' : 'CHT') .' </td>
        </tr>
      ';
    }
  }
}

$result['status'] = 1;
$result['html'] = '
<style>
  th, td {
    padding: 5px;
  }
</style>
<table border="1" style="border-collapse: collapse; width: 100%">
  <tr>
    <th colspan="3"> Công việc ngày '. date('d/m/Y', $data['thoigian']) .' của '. $data['fullname'] .' </th>
  </tr>
  <tr>
    <th style="width: 1px"> STT </th>
    <th> Mục tiêu </th>
    <th style="width: 1px"> Tình trạng </th>
  </tr>
  '. $html .'
</table>
';
