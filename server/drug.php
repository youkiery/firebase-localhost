<?php
class Drug extends Module {
  function __construct() {
    parent::__construct();
    $this->module = 'drug';
    $this->prefix = 'pet_' . $this->table .'_'. $this->module;
    $this->role = $this->getRole();
  }

  function check() {
    $sql = 'select * from pet_test_permission where userid = '. $this->userid;
    $query = $this->db->query($sql);
    if (empty($query->fetch_assoc())) return 0;
    return 1;
  }

  /**
   * filter => name, effect, target
   */
  function filter($filter) {
    $xtra = array();
    if (!empty($filter['name'])) $xtra []= 'name like "%'. $filter['name'] .'%"';
    if (!empty($filter['effect'])) $xtra []= 'effect like "%'. $filter['effect'] .'%"';
    if (count($xtra)) $xtra = 'where '. implode(' and ', $xtra);
    else $xtra = '';
  
    $sql = 'select * from pet_test_heal_medicine '. $xtra .' order by name limit 30';
    $query = $this->db->query($sql);
    $list = array();

    while ($row = $query->fetch_assoc()) {
      $row['limits'] = urldecode($row['limits']);
      $row['effect'] = urldecode($row['effect']);
      $row['sideeffect'] = urldecode($row['sideeffect']);
      $row['mechanic'] = urldecode($row['mechanic']);
      $list []= $row;
    }
    return $list;
  }

  function filter2($filter) {
    $xtra = array();
    if (!empty($filter->name)) $xtra []= 'name like "%'. $filter->name .'%"';
    if (!empty($filter->effect)) $xtra []= 'effect like "%'. $filter->effect .'%"';
    if (count($xtra)) $xtra = 'where '. implode(' and ', $xtra);
    else $xtra = '';
  
    $sql = 'select * from pet_test_heal_medicine '. $xtra .' order by name limit 30';
    $query = $this->db->query($sql);
    $list = array();

    while ($row = $query->fetch_assoc()) {
      $row['limits'] = urldecode($row['limits']);
      $row['effect'] = urldecode($row['effect']);
      $row['sideeffect'] = urldecode($row['sideeffect']);
      $row['mechanic'] = urldecode($row['mechanic']);
      $list []= $row;
    }
    return $list;
  }

  function insert($data) {
    $sql = 'select * from pet_test_heal_medicine where name = "'. $data->name .'" limit 1';
    $query = $this->db->query($sql);
    if (empty($query->fetch_assoc())) {
      $sql = "insert into pet_test_heal_medicine (code, name, unit, system, limits, effect, effective, disease, note, sideeffect, mechanic) values('', '$data->name', '', '', '$data->limit', '$data->effect', '', '', '', '$data->sideeffect', '$data->mechanic')";
      $this->db->query($sql);
      return '';
    }
    return 'Tên thuốc đã tồn tại';
  }

  function update($data) {
    $sql = 'select * from pet_test_heal_medicine where name = "'. $data['name'] .'" and id <> '. $data['id'] .' limit 1';
    $query = $this->db->query($sql);
    if (empty($query->fetch_assoc())) {
      $sql = "update pet_test_heal_medicine set name = '$data[name]', limits = '$data[limit]', effect = '$data[effect]', sideeffect = '$data[sideeffect]', mechanic = '$data[mechanic]' where id = ". $data['id'];
    //   die($sql);
      $this->db->query($sql);
      return '';
    }
    return 'Tên thuốc đã tồn tại';
  }

  function remove($id) {
    $sql = 'delete from pet_test_heal_medicine where id = '. $id;
    $query = $this->db->query($sql);
  }

  function select($id) {
    $sql = 'select * from pet_test_heal_medicine where id = '. $id;
    $query = $this->db->query($sql);

    return $query->fetch_assoc();
  }
}
