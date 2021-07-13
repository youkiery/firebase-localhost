<?php
class Target extends Module {
  function __construct() {
    parent::__construct();
    $this->module = 'target';
    $this->prefix = 'pet_' . $this->table .'_'. $this->module;
    $this->role = $this->getRole();
  }

  public function init($key = '') {
    $sql = 'select * from pet_test_target where active = 1 and name like "%'. $key .'%" order by id asc ';
    $query = $this->db->query($sql);
    $list = array();

    while ($row = $query->fetch_assoc()) {
      $list []= $row;
    }
    return $list;
  }

  public function insert($data) {
    $sql = 'select * from pet_test_target where name = "'. $data['name'] .'"';
    $query = $this->db->query($sql);
    if (empty($row = $query->fetch_assoc())) {
      $sql = "insert into pet_test_target (name, number, active, unit, intro, flag, up, down, disease, aim) values('$data[name]', 0, 1, '$data[unit]', '$data[intro]', '$data[flag]', '$data[up]', '$data[down]', '$data[disease]', '$data[aim]')";
    }
    else {
      $sql = "update pet_test_target set name = '$data[name]', active = 1, unit = '$data[unit]', intro = '$data[intro]', flag = '$data[flag]', up = '$data[up]', down = '$data[down]', disease = '$data[disease]', aim = '$data[aim]' where id = $row[id]";
    }
    $query = $this->db->query($sql);
  }

  public function remove($id) {
    $sql = 'update pet_test_target set active = 0 where id = '. $id;
    $query = $this->db->query($sql);
  }

  public function update($id) {
    $sql = 'update pet_test_target set number = number + 1 where id = '. $id;
    $this->db->query($sql);
  }

  public function reset($id) {
    $sql = 'update pet_test_target set number = 0 where id = '. $id;
    $this->db->query($sql);
  }
}
