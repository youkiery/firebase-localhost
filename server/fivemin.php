<?php
class Fivemin extends Module {
  function __construct() {
    parent::__construct();
    $this->module = '5min';
    $this->prefix = 'pet_' . $this->table .'_'. $this->module;
    $this->role = $this->getRole();
  }

  function thisrole() {
    $sql = 'select * from pet_test_permission where userid = '. $this->userid .' and module = "kaizen"';
    $query = $this->db->query($sql);
    $role = $query->fetch_assoc();
    return $role['type'];
  }

  public function init($filter) {
    $filter['time'] = $filter['time'] / 1000;
    $first_week = strtotime('monday this week', $filter['time'] );
    $xtra = '';
    if ($this->thisrole() < 2) $xtra = ' and nhanvien = '. $this->userid;

    $sql = 'select a.*, concat(last_name, " ", first_name) as nhanvien from pet_test_5min a inner join pet_users b on a.nhanvien = b.userid where thoigian > '. $first_week . $xtra .' order by thoigian desc';
    $query = $this->db->query($sql);

    $list = array();
    while ($row = $query->fetch_assoc()) {
      $row['thoigian'] *= 1000;
      $list []= $row;
    }
    return $list;
  }

  public function get($id) {
    $sql = 'select * from pet_test_5min_hang where idcha = '. $id;
    $query = $this->db->query($sql);
    $data = array();

    while ($row = $query->fetch_assoc()) {
      if (empty($data[$row['tieuchi']])) $data[$row['tieuchi']] = array();
      $row['hoanthanh'] = intval($row['hoanthanh']);
      $data[$row['tieuchi']] []= $row;
    }

    return $data;
  }

  public function change($id, $status) {
    if ($status) $status = time();
    $sql = 'update pet_test_5min_hang set hoanthanh = '. $status.' where id = '. $id;
    $this->db->query($sql);
  }

  public function update($data) {
    $sql = "update pet_test_5min set chamsoc = '$data[chamsoc]', tugiac = '$data[tugiac]', giaiphap = '$data[giaiphap]', uytin = '$data[uytin]', ketqua = '$data[ketqua]', dongdoi = '$data[dongdoi]', trachnhiem = '$data[trachnhiem]', tinhyeu = '$data[tinhyeu]' where id = $data[id]";
    $this->db->query($sql);
  }

  public function insert($data) {
    $time = time();
    $sql = "insert into pet_test_5min (nhanvien, thoigian) values ($this->userid, ". time() .")";
    $this->db->query($sql);
    $id = $this->db->insert_id;

    foreach ($data as $name => $value) {
      $list = explode(',', $value);
      foreach ($list as $key => $real_value) {
        $sql = "insert into pet_test_5min_hang (idcha, noidung, tieuchi, hoanthanh) values($id, '$real_value', '$name', 0)";
        $this->db->query($sql);
      }
    }
  }
}
