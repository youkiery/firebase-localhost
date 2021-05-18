<?php
class Vaccine extends Module {
  function __construct() {
    parent::__construct();
    $this->module = 'vaccine';
    $this->prefix = 'pet_' . $this->table .'_'. $this->module;
    $this->role = $this->getRole();
  }

  function thisrole() {
    $sql = 'select * from pet_test_permission where userid = '. $this->userid .' and module = "vaccine"';
    $query = $this->db->query($sql);
    $role = $query->fetch_assoc();
    return $role['type'];
  }

  function getList($filter) {
    $list = array();
    $data = array();
    $disease = $this->getDiseaseList();

    $time = time();
    $limit = $time + 60 * 60 * 24 * 14;

    $sql = 'select * from `'. $this->prefix .'` where calltime < '. $limit .' and status = '. $filter['status'] .' order by calltime desc '. ($filter['status'] > 0 ? ' limit 50' : '');
    $query = $this->db->query($sql);

    // tên thú cưng, sđt, vaccine, ngày tái chủng, ghi chú, trạng thại
    while ($row = $query->fetch_assoc()) {
      if ($time > $row['calltime']) $row['color'] = 'red';
      else $row['color'] = 'green';
      $list []= $row;
    }

    usort($list, "cmp2");

    // tên thú cưng, sđt, vaccine, ngày tái chủng, ghi chú, trạng thại
    foreach ($list as $row) {
      $pet = $this->getPetId($row['petid']);
      $customer = $this->getCustonerId($pet['customerid']);
      if (!empty($customer['phone'])) {
        $data []= array(
          'id' => $row['id'],
          'petname' => $pet['name'],
          'name' => $customer['name'],
          'number' => $customer['phone'],
          'vaccine' => $disease[$row['diseaseid']],
          'calltime' => $row['calltime'],
          'note' => $row['note'],
          'color' => $row['color'],
        );
      }
    }
    return $data;
  }

  function getDiseaseList() {
    $list = array();
    $sql = 'select * from `pet_'. $this->table .'_disease`';
    $query = $this->db->query($sql);

    while ($row = $query->fetch_assoc()) {
      $list[$row['id']] = $row['name'];
    }
    return $list;
  }

  function diseaseList() {
    $list = array();
    $sql = 'select * from `pet_'. $this->table .'_disease`';
    $query = $this->db->query($sql);

    while ($row = $query->fetch_assoc()) {
      $list []= array(
        'id' => $row['id'],
        'name' => $row['name']
      );
    }
    return $list;
  }

  function getCustonerId($cid) {
    if (!empty($cid)) {
      $sql = 'select * from `pet_'. $this->table .'_customer` where id = ' . $cid;
      $query = $this->db->query($sql);
  
      if (!empty($row = $query->fetch_assoc())) return $row;
    }
    return array('phone' => '');
  }

  function getPetId($pid) {
    if (!empty($pid)) {
      $sql = 'select * from `pet_'. $this->table .'_pet` where id = ' . $pid;
      $query = $this->db->query($sql);
  
      if (!empty($row = $query->fetch_assoc())) return $row;
    }
    return array('customerid' => 0);
  }

  function getUserNotify($page = 1) {
    $list = array();
    // lấy danh sách thông báo
    $xtra = '';
    if ($this->thisrole() < 2) {
      // nhân viên, lấy thông báo bản thân
      $xtra = 'where userid = ' . $this->userid;
    }
    $sql = 'select * from `pet_'. $this->table .'_notify` ' . $xtra . ' order by time desc';
    $query = $this->db->query($sql);

    while ($row = $query->fetch_assoc()) {
      $list []= $this->parseWorkNotify($row);
    }
    return $list;
  }

  function getUserNotifyTime() {
    $sql = 'select * from `pet_'. $this->table .'_notify_read` where userid = ' . $this->userid;
    $query = $this->db->query($sql);

    if (empty($row = $query->fetch_assoc())) {
      $sql = 'insert into `pet_'. $this->table .'_notify_read` (userid, time) values ('. $this->userid .', 0)';
      $this->db->query($sql);
      $row = array(
        'time' => 0
      );
    }
    return $row['time'];
  }

  // userid, action, workid, time
  function parseWorkNotify($data) {
    $action_trans = array(1 => 'thêm công việc', 'cập nhật tiến độ', 'hoàn thành', 'hủy công việc');
    $user = checkUserId($data['userid']);
    $name = (!empty($user['last_name']) ? $user['last_name'] . ' ': '') . $user['first_name'];
    $work = $this->getWorkById($data['workid']);

    return array(
      'id' => $data['workid'],
      'content' => $name . ' ' . $action_trans[$data['action']] . ' ' . $work['content'],
      'time' => date('d/m/Y H:i', $data['time'])
    );
  }

  function getWorkById($workid) {
    $sql = 'select * from `'. $this->prefix .'` where id = ' . $workid;
    $query = $this->db->query($sql);
    return $query->fetch_assoc();
  }

  function checkWorkId($workid) {
    $sql = 'select * from `'. $this->prefix .'` where id = '. $workid;
    $query = $this->db->query($sql);

    if (!empty($query->fetch_assoc())) return true;
    return false;
  }

  function insertWork($data, $time) {
    $sql = 'insert into `'. $this->prefix .'` (cometime, calltime, last_time, post_user, edit_user, userid, depart, customer, content, process, confirm, review, note) value("'. $data['cometime'] .'", "'. $data['calltime'] .'", '. $time .', '. $this->userid .', '. $this->userid .', '. $data['employ'] .', 0, 0, "'. $data['content'] .'", 0, 0, "", "")';

    if ($this->db->query($sql)) {
      $id = $this->db->insert_id;
      $this->insertNotify(INSERT_NOTIFY, $id, $time);
      $this->setLastUpdate($time);
    }
  }

  function updateWork($data, $time) {
    $xtra = '';
    if ($this->thisrole() > 1) $xtra .= ', calltime = ' . $data['calltime'] . ', content = "' . $data['content'] . '"';

    $sql = 'update `'. $this->prefix .'` set process = '. $data['process'] .', note = "'. $data['note'] .'", image = "'. $data['image'] .'" '. $xtra .' where id = '. $data['id'];
    if ($this->db->query($sql)) {
      if ($data['process'] == 100) $this->insertNotify(COMPLETE_NOTIFY, $data['id'], $time);
      else $this->insertNotify(EDIT_NOTIFY, $data['id'], $time);
      $this->setLastUpdate($time);
    }
  }

  function doneWork($data, $time) {
    $xtra = '';
    $sql = 'update `'. $this->prefix .'` set process = 100 where id = '. $data['id'];
    if ($this->db->query($sql)) {
      $this->insertNotify(EDIT_NOTIFY, $data['id'], $time);
      $this->setLastUpdate($time);
    }
  }

  function removeWork($data, $time) {
    $sql = 'update `'. $this->prefix .'` set active = 0 where id = '. $data['id'];
    if ($this->db->query($sql)) {
      $this->setLastUpdate($time);
      $this->insertNotify(REMOVE_NOTIFY, $data['id'], $time);
    }
  }
}
