<?php
function checkUserid() {
  global $db, $data;

  $sql = "select * from pet_test_user where session = '$data->session'";
  $user = $db->fetch($sql);
  return $user['userid'];
}

function totime($date) {
  if (preg_match("/^([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4})$/", $date, $m)) {
    $date = mktime(0, 0, 0, $m[2], $m[1], $m[3]);
  }
  else return false;
  return $date;
}
