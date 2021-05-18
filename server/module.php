<?php 
class Module {
  public $db;
  public $table;
  public $module;
  public $prefix;
  public $userid;
  public $role;

  function __construct() {
    global $mysqli, $userid, $branch;

    $this->db = $mysqli;
    $this->userid = $userid;
    $this->branchid = 0;
    $this->table = $branch;
  }

  function setLastRead($time) {
    $read = $this->checkLastRead();
    
    if ($read) $sql = 'update `pet_'. $this->table .'_notify_read` set time = '. $time .' where userid = '. $this->userid . ' and module = "'. $this->module .'"';
    else $sql = 'insert into `pet_'. $this->table .'_notify_read` (userid, module, time) values ('. $this->userid .',  "'. $this->module .'", '. $time .')';
    // die($sql);
    $this->db->query($sql);
  }

  function checkLastRead() {
    $sql = 'select * from `pet_'. $this->table .'_notify_read` where module = "'. $this->module .'" and userid = '. $this->userid;
    $query = $this->db->query($sql); 
    $read = $query->fetch_assoc();
    if (!empty($read)) {
      return $read['time'];
    }
    return 0;
  }

  function employ_list() {
    $list = array();
    $sql = 'select a.userid, b.username as username, concat(last_name, " ", first_name) as name from `pet_'. $this->table .'_user` a inner join `pet_users` b on a.userid = b.userid group by userid';

    $query = $this->db->query($sql);
    while ($row = $query->fetch_assoc()) {
      $list[$row['userid']] = $row['name'];
    }
    return $list;
  }
  
  function insertNotify($action, $targetid, $time) {
    $sql = 'insert into `pet_'. $this->table .'_notify` (userid, action, workid, module, time) values ('. $this->userid .', '. $action .', '. $targetid .', "'. $this->module .'", '. $time .')';
    $this->db->query($sql);
    $this->setLastUpdate($time);
  }

  function setLastUpdate($time) {
    $sql = 'update `pet_'. $this->table .'_notify_last` set time = "'. $time .'" where module = "'. $this->module .'"';
    $this->db->query($sql);
  }

  function checkLastUpdate($time) {
    $config = $this->getLastUpdate();

    if ($config > $time) return true;
    return false;
  }

  function getLastUpdate() {
    $sql = 'select * from `pet_'. $this->table .'_notify_last` where module = "'. $this->module .'"';
    $query = $this->db->query($sql);
    $config = $query->fetch_assoc();
    if (empty($config)) {
      $sql = 'insert into `pet_'. $this->table .'_notify_last` (module, time) values ("'. $this->module .'", 0)';
      $this->db->query($sql);
      $config = array('time' => 0);
    }

    return intval($config['time']);
  }

  function getNotifyTime() {
    $sql = 'select * from `pet_'. $this->table .'_notify_read` where module = "'. $this->module .'" and userid = ' . $this->userid;
    $query = $this->db->query($sql);

    if (empty($row = $query->fetch_assoc())) {
      $sql = 'insert into `pet_'. $this->table .'_notify_read` (userid, module, time) values ('. $this->userid .', "'. $this->module .'", 1)';
      $this->db->query($sql);
      $row = array(
        'time' => 1
      );
    }
    return $row['time'];
  }

  function getNotifyUnread() {
    $time = $this->getNotifyTime();

    $xtra = '';
    if (!$this->role) $xtra = 'and userid = '. $this->userid;
    $sql = 'select id from `pet_'. $this->table .'_notify` where module = "'. $this->module .'" and time > ' . $time . ' ' . $xtra;
    // die($sql);
    $query = $this->db->query($sql);

    return $query->num_rows;
  }

  function getRole() {
    global $action;
    // kiểm tra thời gian sử dụng chức năng, trừ lúc login
    if ($action !== 'login') {
      $sql = 'select * from `pet_setting_config_module` where branchid = ' . $this->branchid . ' and module = "'. $this->module .'"';
      $query = $this->db->query($sql);
      $config = $query->fetch_assoc();

      if (empty($config)) {
        $sql = 'insert into `pet_setting_config_module` (branchid, module, start, end) values ("'. $this->branchid .'", "'. $this->module .'", "0-0", "0-0")';
        $this->db->query($sql);
        $config = array(
          'branchid' => $this->branchid,
          'module' => $this->module,
          'start' => '0-0',
          'end' => '0-0'
        );
      }

      $time = time();
      $start = explode('-', $config['start']);
      $end = explode('-', $config['end']);
      $start = strtotime($start[0] . ':' . $start[1]);
      $end = strtotime($end[0] . ':' . $end[1]);
  
      if ($start !== $end && ($time < $start || $time > $end)) {
        echo json_encode(array(
          'overtime' => 1,
        ));
        die();
      }
    }

    // kiểm tra quyền sử dụng
    $sql = 'select * from `pet_'. $this->table .'_permission` where module = "'. $this->module .'" and userid = '. $this->userid;
    $query = $this->db->query($sql);

    $user = $query->fetch_assoc();
    if (!empty($user) && $user['type']) return 1;
    return 0;
  }

  function checkOvertime() {
    // lấy config module theo userid
    $time = time();
    $sql = 'select * from `pet_'. $this->table .'_config_time` where userid = '. $this->userid .' and module = "'. $this->module .'"';
    $query = $this->db->query($sql);
    $userconfig = $query->fetch_assoc();

    if (!empty($userconfig)) {
      if ($time < $userconfig['start'] || $time > $userconfig['end']) return 1;
      return 0;
    }
    else {
      // lấy config module theo vai trò
      $sql = 'select * from `pet_'. $this->table .'_config_module` where module = "'. $this->module .'" order by role desc';
      $query = $this->db->query($sql);
      $moduleconfig = $query->fetch_assoc();
      if ($time < $moduleconfig['start'] || $time > $moduleconfig['end']) return 1;
      return 0;
    }
    // không có config
    return 0;
  }

  function getUserById($id) {
    global $mysqli;
    $sql = 'select * from `pet_users` where userid = '. $id;
    $query = $mysqli->query($sql);
    return $query->fetch_assoc();
  }

  function getUserList($daily = false) {
    $list = array();
    $xtra = '';
    if ($daily) $xtra = 'where daily = 1';
  
    $sql = 'select * from `pet_'. $this->table .'_user`' . $xtra;
    $query = $this->db->query($sql);
  
    while($row = $query->fetch_assoc()) {
      $user = checkUserId($row['userid']);
      $list[$row['userid']] = (!empty($user['last_name']) ? $user['last_name'] . ' ': '') . $user['first_name'];
    }
    return $list;
  }
}
