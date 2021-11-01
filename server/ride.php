<?php
function init() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['from'] = date('01/m/Y');
  $result['end'] = date('d/m/Y');
  $result['list'] = getlist();
  return $result;
}

function getlist($today = false) {
  global $db, $data;

  // $sql = "select * from pet_test"

  return $list;
}
