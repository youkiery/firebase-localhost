<?php
function init() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['list'] = getlist();
  return $result;
}

function remove() {
  global $data, $db, $result;

  if ($data->segment == '0') $sql = "delete from pet_test_";

  $result['status'] = 1;
  $result['list'] = getlist();
  return $result;
}

function getlist($today = false) {
  global $db, $data;

  $data->start = isodatetotime($data->start);
  $data->end = isodatetotime($data->end);
  $ride = "select * from pet_test_ride where (time between $data->start and $data->end)";
  $pay = "select * from pet_test_import where module = 'ride' and (time between $data->start and $data->end)";

  return array(
    0 => $db->all($ride),
    $db->all($pay),
  );
}
