<?php 

$id = parseGetData('id', '');
$list = explode(',', $id);

$start = parseGetData('start', '');
$end = parseGetData('end', '');
$starttime = strtotime(date('Y/m/d', $start / 1000));
$endtime = strtotime(date('Y/m/d', $end / 1000)) + 60 * 60 * 24 - 1;

$html = array();

foreach ($list as $id) {
  $sql = 'select a.*, concat(last_name, " ", first_name) as fullname from pet_test_5min a inner join pet_users b on a.nhanvien = b.userid where a.nhanvien = '. $id . ' and (thoigian between '. $starttime. ' and '. $endtime  .')';
  $query = $mysqli->query($sql);
  
  while ($data = $query->fetch_assoc()) {
    $html_temp = '';
    $sql = 'select * from pet_test_5min_hang where idcha = '. $data['id'];
    $query2 = $mysqli->query($sql);
    $index = 1;
    
    while ($row = $query2->fetch_assoc()) {
      $html_temp .= '
        <tr>
          <td>'. ($index++) .'</td>
          <td><b>'. ($row['tieuchi'] .':</b> '. $row['noidung']) .'</td>
          <td>'. ($row['hoanthanh'] ? 'HT' : 'Chưa HT') .'</td>
        </tr>
      ';
    }
    $html []= '
    <table border="1">
      <tr>
        <th colspan="3"> Công việc ngày '. date('d/m/Y', $data['thoigian']) .' của '. $data['fullname'] .' </th>
      </tr>
      <tr>
        <th> STT </th>
        <th> Mục tiêu </th>
        <th> Tình trạng </th>
      </tr>
      '. $html_temp .'
    </table>';
  }
}

$result['status'] = 1;
$result['html'] = '
<style>
@media print{
  @page {
    size: A4 portrait;
    margin: 0.5in;
  }
}
th, td {
  padding: 5px;
}
table {
  border-collapse: collapse;
  width: 100%;
  margin-botton: 20px;
  margin-bottom: 20px;
  border: 2px solid black;
}
</style>
'. implode('', $html);
