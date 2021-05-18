<?php
require_once(ROOTDIR .'/ride.php');
$ride = new Ride();

$type = parseGetData('type', 0);
if ($type) {
  $data = array(
    'amount' => parseGetData('amount', 0),
    'note' => parseGetData('note', '')
  );

  $sql = "insert into `" . $ride->prefix . "` (type, driver_id, doctor_id, customer_id, amount, clock_from, clock_to, destination, note, time) values (1, $userid, 0, 0, $data[amount], 0, 0, '', '$data[note]', " . time() . ")";
}
else { 
  $data = array(
    'doctorid' => parseGetData('doctorid', ''),
    'from' => (float) (str_replace(',', '.', parseGetData('from', '0'))),
    'end' => (float) (str_replace(',', '.', parseGetData('end', '0'))),
    'amount' => parseGetData('amount', 0),
    'destination' => parseGetData('destination', ''),
    'note' => parseGetData('note', '')
  );

  if ($data['from'] > $data['end']) {
    $temp = $data['from'];
    $data['from'] = $data['end'];
    $data['end'] = $temp;
  }

  $ride->setClock($data['end']);

  $sql = "insert into `" . $ride->prefix . "` (type, driver_id, doctor_id, customer_id, amount, clock_from, clock_to, price, destination, note, time) values (0, $userid, $userid, $data[doctorid], '$data[amount]', $data[from], $data[end], 0, '$data[destination]', '$data[note]', " . time() . ")";
  $result['end'] = $data['end'];
}

$ride->db->query($sql);
$result['status'] = 1;

