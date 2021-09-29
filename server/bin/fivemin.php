<?php
class Fivemin extends Module {
  function __construct() {
    parent::__construct();
    $this->module = '5min';
    $this->prefix = 'pet_' . $this->table .'_'. $this->module;
    $this->role = $this->getRole();
  }

  function thisrole() {
    $sql = 'select * from pet_test_user_per where userid = '. $this->userid .' and module = "kaizen"';
    $query = $this->db->query($sql);
    $role = $query->fetch_assoc();
    return $role['type'];
  }

  public function init($filter) {
    $sql = 'select a.* from pet_test_5min a inner join pet_users b on a.nhanvien = b.userid where nhanvien = '. $this->userid .' order by thoigian desc limit 10 offset '. ($filter['page'] - 1) * 10;
    $query = $this->db->query($sql);

    $yesterday = strtotime(date('Y/m/d', time() - 60 * 60 * 24). ' 19:00');

    $list = array();
    while ($row = $query->fetch_assoc()) {
      $row['dis'] = 0;
      if ($row['thoigian'] <= $yesterday) $row['dis'] = 1;
      $row['thoigian'] *= 1000;
      $list []= $row;
    }
    return $list;
  }

  public function get($id, $act = 0) {
    $sort = '';
    if ($act) $sort = 'order by thoigian asc';
    $sql = 'select * from pet_test_5min_hang where idcha = '. $id .' '. $sort;
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

  public function rate($data) {
    $sql = 'update pet_test_5min_hang set sao = '. $data->point .' where id = '. $data->id;
    $this->db->query($sql);
  }

  public function hoanthanh($filter) {
    $starttime = strtotime(date('Y/m/d', $filter['start'] / 1000));
    $endtime = strtotime(date('Y/m/d', $filter['end'] / 1000)) + 60 * 60 * 24 - 1;

    $sql = 'select a.*, concat(last_name, " ", first_name) as nguoigopy from pet_test_5min a inner join pet_users b on a.nguoigopy = b.userid where nhanvien = '. $filter['nhanvien'] .' and (thoigian between '. $starttime. ' and '. $endtime  .') order by id desc';
    // die($sql);
    $query = $this->db->query($sql);

    $list = array();
    while ($row = $query->fetch_assoc()) {
      $data = array(
        'id' => $row['id'],
        'time' => $row['thoigian'],
        'gopy' => $row['gopy'],
        'nguoigopy' => $row['nguoigopy'],
        'dulieu' => array()
      );
      $sql = 'select * from pet_test_5min_hang where idcha = '. $row['id'];
      $query2 = $this->db->query($sql);
      $temp = array();
      while ($hang = $query2->fetch_assoc()) {
        if ($hang['noidung'] !== 'undefined' && strlen($hang['noidung']))

        if (empty($temp[$hang['tieuchi']])) $temp[$hang['tieuchi']] = array();
        $temp[$hang['tieuchi']] []= array(
          'id' => $hang['id'],
          'sao' => $hang['sao'],
          'noidung' => $hang['noidung'],
          'hoanthanh' => $hang['hoanthanh'],
          'image' => $hang['hinhanh']
        );
      }
      foreach ($temp as $key => $value) {
        $data['dulieu'] []= array(
          'tieuchi' => $key,
          'danhsach' => $value
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
      $sql = 'select hoanthanh, sao from pet_test_5min_hang where idcha = '. $row['id'];
      $query2 = $this->db->query($sql);
      while ($nhanvien = $query2->fetch_assoc()) {
        if (empty($data[$row['nhanvien']])) $data[$row['nhanvien']] = array(
          'nhanvien' => $row['hoten'],
          'danhgia' => 0,
          'hoanthanh' => 0,
          'chuahoanthanh' => 0
        );
        $data[$row['nhanvien']]['danhgia'] += intval($nhanvien['sao']);
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
        'danhgia' => $row['danhgia'],
        'chuahoanthanh' => $row['chuahoanthanh']
      );
    }

    usort($list, 'cmp3');
    return $list;
  }

  public function upload($id, $image, $lydo, $hoanthanh) {
    $sql = 'update pet_test_5min_hang set hinhanh = "'. str_replace('@@', '%2F', $image).'", lydo = "'. addslashes($lydo) .'", hoanthanh = "'. ($hoanthanh > 0 ? time() : 0) .'" where id = '. $id;
    $this->db->query($sql);
    return 1;
  }

  public function getParentData($id) {
    $sql = 'select * from pet_test_5min_hang where id = '. $id;
    $query = $this->db->query($sql);
    $data = $query->fetch_assoc();

    $sql = 'select * from pet_test_5min where id = '. $data['idcha'];
    $query = $this->db->query($sql);
    return $query->fetch_assoc();
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

  public function getid($id) {
    $sql = 'select * from pet_test_5min where id = '. $id;
    $query = $this->db->query($sql);

    $data = $query->fetch_assoc();
    $data['thoigian'] *= 1000;
    return $data;
  }

  public function insert($data) {
    $time = time();
    $hour = date('H', $time);
    if ($hour >= 19) $time += 60 * 60 * 24;
    $sql = "insert into pet_test_5min (nhanvien, thoigian) values ($this->userid, ". $time .")";
    $this->db->query($sql);
    $id = $this->db->insert_id;

    foreach ($data as $name => $list) {
      foreach ($list as $key => $field) {
        if (!empty($field) && !empty($field->giatri)) {
          $sql = "insert into pet_test_5min_hang (idcha, noidung, tieuchi, thoigian, hoanthanh) values($id, '$field->giatri', '$name', $field->thoigian, 0)";
          $this->db->query($sql);
        }
      }
    }
    return $this->getid($id);
  }

  public function update($data, $id) {
    foreach ($data as $name => $list) {
      foreach ($list as $key => $field) {
        if (!empty($field) && !empty($field->giatri)) {
          if ($field->id) $sql = "update pet_test_5min_hang set noidung = '$field->giatri', thoigian = $field->thoigian where id = $field->id";
          else $sql = "insert into pet_test_5min_hang (idcha, noidung, tieuchi, thoigian, hoanthanh) values($id, '$field->giatri', '$name', $field->thoigian, 0)";
          $this->db->query($sql);
        }
      }
    }
    return $this->getid($id);
  }
}
