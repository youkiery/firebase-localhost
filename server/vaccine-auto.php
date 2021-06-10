 <?php 

require_once(ROOTDIR .'/vaccine.php');
$vaccine = new Vaccine();

$filter = array(
  'status' => parseGetData('status', 0)
);

$start = strtotime(date('Y/m/d'));
$end = time();

$sql = 'select * from pet_test_vaccine where (ctime between '. $start . ' and '. $end . ') and status = 0 limit 50';

$query = $mysqli->query($sql);
$list = array();

$disease = $vaccine->getDiseaseList();

while ($row = $query->fetch_assoc()) {
  $pet = $vaccine->getPetId($row['petid']);
  $customer = $vaccine->getCustonerId($pet['customerid']);
  if (!empty($customer['phone'])) {
    $list []= array(
      'name' => $customer['name'],
      'number' => $customer['phone'],
      'vaccine' => $disease[$row['diseaseid']],
      'calltime' => $row['calltime'],
    );
  }
}

$result['status'] = 1;
$result['data'] = $vaccine->getList($filter);
$result['new'] = $list;

echo json_encode($result);
die();
