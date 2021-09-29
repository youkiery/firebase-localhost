<?php 
class Kaizen extends Module {
  public $type;
  function __construct() {
    parent::__construct();
    $this->type = array(
      'undone' => 0,
      'done' => 1,
    );
    $this->module = 'kaizen';
    $this->prefix = 'pet_' . $this->table .'_'. $this->module;
    $this->role = $this->getRole();
  }

  function thisrole() {
    $sql = 'select * from pet_test_user_per where userid = '. $this->userid .' and module = "kaizen"';
    $query = $this->db->query($sql);
    $role = $query->fetch_assoc();
    return $role['type'];
  }

  function getKaizenList() {
    global $filter; 
    $xtra = array();

    $tick = 0;
    if (!empty($filter['starttime'])) {
      $filter['starttime'] = totime($filter['starttime']);
      $tick += 1;
    }
    if (!empty($filter['endtime'])) {
      $filter['endtime'] = totime($filter['endtime']) + 60 * 60 * 24 - 1;
      $tick += 2;
    }

    switch ($tick) {
      case 1:
        $xtra []= '(edit_time >= '. $filter['starttime'] .')';
      break;
      case 2:
        $xtra []= '(edit_time <= '. $filter['endtime'] .')';
      break;
      case 3:
        $xtra []= '(edit_time between '. $filter['starttime'] .' and '. $filter['endtime'] .')';
      break;
    }

    if (!empty($filter['keyword'])) $xtra []= '((result like "%'. $filter['keyword'] .'%") or (solution like "%'. $filter['keyword'] .'%") or (problem like "%'. $filter['keyword'] .'%"))';
    if ($this->thisrole() < 2) $xtra []= 'userid = ' . $this->userid;
    if (count($xtra)) $xtra = ' and ' . implode(' and ', $xtra);
    else $xtra = '';
    $list = array();
    $sql = 'select * from `pet_test_kaizen` where active = 1 ' . $xtra . ' and done = '. $filter['type'] .' order by edit_time ' . $filter['sort'] . ' limit 10 offset '. ($filter['page'] - 1) * 10;
    $query = $this->db->query($sql);

    while ($row = $query->fetch_assoc()) {
      $user = checkUserId($row['userid']);
      $name = (!empty($user['last_name']) ? $user['last_name'] . ' ': '') . $user['first_name'];
      $data = array(
        'id' => $row['id'],
        'name' => $name,
        'done' => intval($row['done']),
        'problem' => $row['problem'],
        'solution' => $row['solution'],
        'result' => $row['result'],
        'time' => date('d/m/Y', $row['edit_time'])
      );
      $list []= $data;
    }
    return $list;
  }

  function initList() {
    global $filter;

    $list = array(
      'done' => array(),
      'undone' => array(),
    );
    $xtra = array();
    $tick = 0;
    if (!empty($filter['starttime'])) {
      $filter['starttime'] = totime($filter['starttime']);
      $tick += 1;
    }
    if (!empty($filter['endtime'])) {
      $filter['endtime'] = totime($filter['endtime']) + 60 * 60 * 24 - 1;
      $tick += 2;
    }
    
    switch ($tick) {
      case 1:
        $xtra []= '(edit_time >= '. $filter['starttime'] .')';
      break;
      case 2:
        $xtra []= '(edit_time <= '. $filter['endtime'] .')';
      break;
      case 3:
        $xtra []= '(edit_time between '. $filter['starttime'] .' and '. $filter['endtime'] .')';
      break;
    }
    
    if (!empty($filter['keyword'])) $xtra []= '((result like "%'. $filter['keyword'] .'%") or (solution like "%'. $filter['keyword'] .'%") or (problem like "%'. $filter['keyword'] .'%"))';
    if ($this->thisrole() < 2) $xtra []= 'userid = ' . $this->userid;
    if (count($xtra)) $xtra = ' and ' . implode(' and ', $xtra);
    else $xtra = '';
    // $list = array();
    
    $sql = 'select * from `pet_test_kaizen` where active = 1 ' . $xtra . ' and done = 1 order by edit_time ' . $filter['sort'] . ' limit '. $filter['page2'] * 10;
    $query = $this->db->query($sql);
    
    while ($row = $query->fetch_assoc()) {
      $user = checkUserId($row['userid']);
      $name = (!empty($user['last_name']) ? $user['last_name'] . ' ': '') . $user['first_name'];
      $data = array(
        'id' => $row['id'],
        'name' => $name,
        'done' => intval($row['done']),
        'problem' => $row['problem'],
        'solution' => $row['solution'],
        'result' => $row['result'],
        'time' => date('d/m/Y', $row['edit_time'])
      );
      $list['done'] []= $data;
    }
    
    $sql = 'select * from `pet_test_kaizen` where active = 1 ' . $xtra . ' and done = 0 order by edit_time ' . $filter['sort'] . ' limit '. $filter['page1'] * 10;
    $query = $this->db->query($sql);
    
    while ($row = $query->fetch_assoc()) {
      $user = checkUserId($row['userid']);
      $name = (!empty($user['last_name']) ? $user['last_name'] . ' ': '') . $user['first_name'];
      $data = array(
        'id' => $row['id'],
        'name' => $name,
        'done' => intval($row['done']),
        'problem' => $row['problem'],
        'solution' => $row['solution'],
        'result' => $row['result'],
        'time' => date('d/m/Y', $row['edit_time'])
      );
      $list['undone'] []= $data;
    }
    return $list;    
  }

  function getKaizenNotify() {
    $sql = 'select * from `pet_test_notify` where module = "kaizen" and userid = '. $this->userid . ' order by time desc';
    $query = $this->db->query($sql);
    $list = array();
    $action_trans = array(1 => 'Thêm giải phảp', 'Cập nhật giải pháp', 'Hoàn thành giải pháp', 'Xóa giải phảp');
    
    while ($row = $query->fetch_assoc()) {
      $user = checkUserId($row['userid']);
      $name = (!empty($user['last_name']) ? $user['last_name'] . ' ': '') . $user['first_name'];
      $kaizen = $this->getKaizenById($row['workid']);
      $list []= array(
        'id' => $row['workid'],
        'content' => $name . ' ' . $action_trans[$row['action']] . ' ' . $kaizen['result'],
        'time' => date('d/m/Y H:i', $row['time'])
      );
    }
    return $list;
  }
  
  function getKaizenById($id) {
    $sql = 'select * from `pet_test_kaizen` where id = ' . $id;
    $query = $this->db->query($sql);
    return $query->fetch_assoc();
  }

  function insertData($data, $time) {
    $sql = 'update `pet_config` set = "'. $time .'" where config_name = "pet_lastkaizen"';
    $this->db->query($sql);

    $sql = 'insert into `pet_test_kaizen` (userid, problem, solution, result, post_time, edit_time) values('. $this->userid .', "'. $data['problem'] .'", "'. $data['solution'] .'", "'. $data['result'] .'", '. time() .', '. time() .')';
    if ($this->db->query($sql)) {
      $this->insertNotify(INSERT_NOTIFY, $this->db->insert_id, $time);
    }
  }

  function updateData($data) {
    $time = time();
    $sql = 'update `pet_test_kaizen` set problem = "'. $data['problem'] .'", solution = "'. $data['solution'] .'", result = "'. $data['result'] .'", edit_time = '. $time .' where id = '. $data['id'];
    // die($sql);
    if ($this->db->query($sql)) {
      $this->insertNotify(EDIT_NOTIFY, $this->db->insert_id, $time);
    }
    return $time;
  }

  function removeData($data) {
    $time = time();
    $sql = 'update `pet_test_kaizen` set active = 0 where id = '. $data['id'];
    if ($this->db->query($sql)) {
      $this->insertNotify(REMOVE_NOTIFY, $data['id'], $time);
    }
    return $time;
  }

  function checkData($id, $type) {
    $time = time();
    $sql = 'update `pet_test_kaizen` set done = '. intval(!$type) .' where id = '. $id;
    if ($this->db->query($sql)) {
      $this->insertNotify(COMPLETE_NOTIFY, $id, $time);
    }
    return $time;
  }
}
