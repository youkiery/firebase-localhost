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
    $sql = 'select a.* from pet_test_5min a inner join pet_users b on a.nhanvien = b.userid where nhanvien = '. $this->userid .' order by thoigian desc limit 10 offset '. ($filter['page'] - 1) * 10;
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

  public function gopy($gopy, $id) {
    $sql = 'update pet_test_5min set gopy = "'. $gopy .'", nguoigopy = '. $this->userid .' where id = '. $id;
    $this->db->query($sql);
    $sql = 'select a.gopy, concat(b.last_name, " ", b.first_name) as nguoigopy from pet_test_5min a inner join pet_users b on a.nhanvien = b.userid where a.id = '. $id;
    $query = $this->db->query($sql);
    $data = $query->fetch_assoc();
    return $data;
  }

  public function hoanthanh($filter) {
    $starttime = strtotime(date('Y/m/d', $filter['start'] / 1000));
    $endtime = strtotime(date('Y/m/d', $filter['end'] / 1000)) + 60 * 60 * 24 - 1;
    $xtra = ' and hoanthanh = 0';
    if ($filter['status']) $xtra = ' and hoanthanh > 0';

    $sql = 'select a.*, concat(last_name, " ", first_name) as nguoigopy from pet_test_5min a inner join pet_users b on a.nguoigopy = b.userid where nhanvien = '. $filter['nhanvien'] .' and (thoigian between '. $starttime. ' and '. $endtime  .') order by thoigian desc';
    // die($sql);
    $query = $this->db->query($sql);

    $list = array();
    while ($row = $query->fetch_assoc()) {
      $data = array(
        'id' => $row['id'],
        'time' => $row['thoigian'],
        'gopy' => $row['gopy'],
        'nguoigopy' => $row['nguoigopy'],
        'danhsach' => array()
      );
      $sql = 'select * from pet_test_5min_hang where idcha = '. $row['id'] . $xtra;
      $query2 = $this->db->query($sql);
      while ($hang = $query2->fetch_assoc()) {
        if ($hang['noidung'] !== 'undefined' && strlen($hang['noidung']))
        $data['danhsach'] []= array(
          'noidung' => $hang['noidung'],
          'tieuchi' => $hang['tieuchi'],
          'image' => $hang['hinhanh']
        );
      }
      $list []= $data;
    }
    return $list;
  }

  public function thongke($filter) {
    $starttime = strtotime(date('Y/m/d', $filter['start'] / 1000));
    $endtime = strtotime(date('Y/m/d', $filter['end'] / 1000)) + 60 * 60 * 24 - 1;

    $sql = 'select a.*, concat(last_name, " ", first_name) as hoten from pet_test_5min a inner join pet_users b on a.nhanvien = b.userid where (thoigian between '. $starttime. ' and '. $endtime  .') order by thoigian desc';
    $query = $this->db->query($sql);

    $data = array();
    while ($row = $query->fetch_assoc()) {
      $sql = 'select hoanthanh from pet_test_5min_hang where idcha = '. $row['id'];
      $query2 = $this->db->query($sql);
      while ($nhanvien = $query2->fetch_assoc()) {
        if (empty($data[$row['nhanvien']])) $data[$row['nhanvien']] = array(
          'nhanvien' => $row['hoten'],
          'hoanthanh' => 0,
          'chuahoanthanh' => 0
        );
        if ($nhanvien['hoanthanh'] > 0) $data[$row['nhanvien']]['hoanthanh'] ++;
        else $data[$row['nhanvien']]['chuahoanthanh'] ++;
      }
    }

    $list = array();
    foreach ($data as $key => $row) {
      $list []= array(
        'id' => $key,
        'nhanvien' => $row['nhanvien'],
        'hoanthanh' => $row['hoanthanh'],
        'chuahoanthanh' => $row['chuahoanthanh']
      );
    }
    return $list;
  }

  public function upload($id, $image, $lydo, $hoanthanh) {
    $sql = 'update pet_test_5min_hang set hinhanh = "'. str_replace('@@', '%2F', $image).'", lydo = "'. addslashes($lydo) .'", hoanthanh = "'. ($hoanthanh > 0 ? time() : 0) .'" where id = '. $id;
    $this->db->query($sql);
  }

  public function change($id, $status) {
    if ($status) $status = time();
    $sql = 'update pet_test_5min_hang set hoanthanh = '. $status.' where id = '. $id;
    $this->db->query($sql);
  }

  public function remove($id) {
    $sql = 'delete from pet_test_5min_hang where idcha = '. $id;
    $this->db->query($sql);
    $sql = 'delete from pet_test_5min where id = '. $id;
    $this->db->query($sql);
  }

  public function update($data) {
    $sql = "update pet_test_5min set chamsoc = '$data[chamsoc]', tugiac = '$data[tugiac]', giaiphap = '$data[giaiphap]', uytin = '$data[uytin]', ketqua = '$data[ketqua]', dongdoi = '$data[dongdoi]', trachnhiem = '$data[trachnhiem]', tinhyeu = '$data[tinhyeu]' where id = $data[id]";
    $this->db->query($sql);
  }

  public function getid($id) {
    $sql = 'select * from pet_test_5min where id = '. $id;
    $query = $this->db->query($sql);

    $data = $query->fetch_assoc();
    $data['thoigian'] *= 1000;
    return $data;
  }

  public function insert($data, $filter) {
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
    return $this->getid($id);
  }
}
