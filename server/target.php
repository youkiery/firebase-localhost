<?php
class Target extends Module {
  function __construct() {
    parent::__construct();
    $this->module = 'target';
    $this->prefix = 'pet_' . $this->table .'_'. $this->module;
    $this->role = $this->getRole();
  }

  public function init() {
    $sql = 'select * from pet_test_target where active = 1 order by id asc ';
    $query = $this->db->query($sql);
    $list = array();

    while ($row = $query->fetch_assoc()) {
      $list []= $row;
    }
    return $list;
  }

  public function insert($name) {
    $sql = 'select * from pet_test_target where name = "'. $name .'"';
    $query = $this->db->query($sql);
    if (empty($row = $query->fetch_assoc())) {
      $sql = 'insert into pet_test_target (name, number) values("'. $name .'", 0)';
    }
    else {
      $sql = 'update pet_test_target set active = 1 where id = '. $row['id'];
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
