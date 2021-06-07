<?php
class Expire extends Module {
  function __construct() {
    parent::__construct();
    $this->module = 'expire';
    $this->prefix = 'pet_' . $this->table .'_'. $this->module;
    $this->role = $this->getRole();
  }

  // filter = timespan by second
  function getList() {
    $start = time(); 
    $end = $start + 60 * 60 * 24 * 30 * 3;
    $half = $start + 60 * 60 * 24 * 30 * 3 / 2;
    $sql = 'select a.*, b.name from `pet_test_storage_expire` a inner join `pet_test_storage_item` b on a.itemid = b.id where status = 0 and a.time < '. $end .' order by time';

    $query = $this->db->query($sql);

    $list = array();
    while ($row = $query->fetch_assoc()) {
      $color = '';
      if ($row['time'] < $start) $color = 'red';
      else if ($row['time'] < $half) $color = 'orange';
      $list []= array(
        'id' => $row['id'],
        'name' => $row['name'],
        'color' => $color,
        'number' => $row['number'],
        'time' => date('d/m/Y', $row['time'])
      );
    }
    return $list;
  }

  function insertStorage($name = '') {
    $sql = 'insert into pet_test_storage (name) values("'. $name .'")';
    $this->db->query($sql);
  }

  function insertItem($data) {
    $sql = 'insert into pet_test_storage_item (code, name, storageid, transfer, purchase, position) values("'. $data->code .'", "'. $data->name .'", "'. $data->storage .'", "'. $data->transfer .'", "'. $data->purchase .'", "'. $data->position .'")';
    $this->db->query($sql);
    return $this->db->insert_id;
  }

  function getItemId($id) {
    $sql = 'select * from pet_test_storage_item where id = '. $id;
    $query = $this->db->query($sql);
    return $query->fetch_assoc();
  }

  function storageList() {
    // $sql = 'select * from pet_test_storage_user where userid = '. $this->userid;
    $sql = 'select * from pet_test_storage';
    $query = $this->db->query($sql);

    $storagelist = array();
    while ($row = $query->fetch_assoc()) $storagelist []= $row;
    return $storagelist;
  }

  function storage() {
    // $sql = 'select * from pet_test_storage_user where userid = '. $this->userid;
    $sql = 'select * from pet_test_storage';
    $query = $this->db->query($sql);

    $storagelist = array();
    while ($row = $query->fetch_assoc()) $storagelist []= $row['id'];
    return $storagelist;
  }

  function item($storagelist) {
    $storage = implode(',', $storagelist);

    $list = array();
    if (count($storagelist)) {
      $sql = 'select * from pet_test_storage_item where storageid in ('. $storage .')';
      $query = $this->db->query($sql);

      while ($row = $query->fetch_assoc()) $list []= $row;
    }

    return $list;
  }

  function getCatalogById($id) {
    $sql = 'select * from `pet_test_catalog` where id = ' . $id;
    $query = $this->db->query($sql);
    return $query->fetch_assoc();
  }

  function remove($id) {
    $sql = 'delete from `pet_test_expire` where id = '. $id;
    $this->db->query($sql);
  }
}
