<?php 

$id = parseGetData('id', '0');

$html = '';

$sql = 'select a.*, concat(last_name, " ", first_name) as fullname from pet_test_5min a inner join pet_users b on a.nhanvien = b.userid where id = '. $id;
$query = $mysqli->query($sql);
$data = $query->fetch_assoc();

$sql = 'select * from pet_test_5min_hang where idcha = '. $id;
$query = $mysqli->query($sql);
$index = 1;

while ($row = $query->fetch_assoc()) {
  $html .= '
    <tr>
      <td>'. ($index++) .'</td>
      <td><b>'. ($row['tieuchi'] .':</b> '. $row['noidung']) .'</td>
      <td>'. ($row['hoanthanh'] ? date('d/m/Y H:i', $row['hoanthanh']) : 'Chưa hoàn thành') .'</td>
    </tr>
  ';
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
    <th> STT </th>
    <th> Mục tiêu </th>
    <th> Tình trạng </th>
  </tr>
  '. $html .'
</table>
';
