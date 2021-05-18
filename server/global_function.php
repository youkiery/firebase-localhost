<?php
function checkUserId($userid) {
    global $mysqli;
    $sql = 'select * from `pet_users` where userid = '. $userid;
    $query = $mysqli->query($sql);

    if (!empty($user = $query->fetch_assoc())) {
        return $user;
    }
    return false;
}

function cmp($source, $target) {
  return strcmp($target['expecttime'], $source['expecttime']);
}

function cmp2($source, $target) {
  return strcmp($target['calltime'], $source['calltime']);
}

function checkUserRole($userid) {
  global $mysqli;
  if (!empty(checkUserId($userid))) {
      $sql = 'select * from `pet_test_user` where userid = '. $userid;
      $query = $mysqli->query($sql);
  
      $user = $query->fetch_assoc();
      if ($user['manager']) return true;
  }
  return false;
}

function getUserBranch($branch) {
  global $userid, $mysqli;
  try {
    $sql = 'select b.* from `pet_setting_user` a inner join `pet_setting_branch` b on a.branch = b.id where b.prefix = "'. $branch .'" and a.userid = ' . $userid;
    $query = $mysqli->query($sql);
    $user = $query->fetch_assoc();

    if (!empty($user)) {
      return array(
        'id' => $user['id'],
        'prefix' => $user['prefix']
      );
    }
    throw new ErrorException('user without branch');
  }
  catch(Exception $e) { 
    echo json_encode(array(
      'status' => 0,
      'no_branch' => 1
    ));
    die();
  }
}

function parseGetData($dataname, $default = '') {
  global $_GET;
  return (isset($_GET[$dataname]) && $_GET[$dataname] != '' ? $_GET[$dataname] : $default);
}

function totime($time) {
  if (preg_match("/^([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4})$/", $time, $m)) {
    $time = mktime(0, 0, 0, $m[2], $m[1], $m[3]);
  }
  else return false;
  return $time;
}
