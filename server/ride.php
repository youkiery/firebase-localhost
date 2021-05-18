<?php
class Ride extends Module {
  function __construct() {
    parent::__construct();
    $this->module = 'ride';
    $this->prefix = 'pet_' . $this->table .'_'. $this->module;
    $this->role = $this->getRole();
    $this->func = array(
      0 => 'parseCollect',
      'parsePay'
    );
  }

  function getList($filter) {
    $time = $filter['time'];
    $from = $time - 60 * 60 * 24 * 15;
    $end = $time + 60 * 60 * 24 * 15;
    $sql = 'select * from `'. $this->prefix .'` where type = "'. $filter['type'] .'" and (time between '. $from .' and '. $end .') order by time desc';
    $query = $this->db->query($sql);
    $list = array();

    while ($row = $query->fetch_assoc()) {
      $func = $this->func[$filter['type']];
      $list []= $this->$func($row);
    }
    return $list;
  }

  function getDataById($id, $type) {
    $sql = 'select * from `'. $this->prefix .'_collect` where id = ' . $id;
    $query = $this->db->query($sql);
    $data = $query->fetch_assoc();
    return $this->func[$type]($data);
  }

  function parseCollect($data) {
    $user = $this->getUserById($data['doctor_id']);
    return array(
      'id' => $data['id'],
      'name' => $user['first_name'],
      'clock_from' => $data['clock_from'],
      'clock_to' => $data['clock_to'],
      'long' => $data['clock_to'] - $data['clock_from'],
      'destination' => $data['destination'],
      'amount' => $data['amount'],
      'note' => $data['note'],
      'time' => date('d/m/Y', $data['time']),
    );
  }

  function parsePay($data) {
    $user = $this->getUserById($data['driver_id']);
    return array(
      'id' => $data['id'],
      'name' => $user['first_name'],
      'amount' => $data['amount'],
      'note' => $data['note'],
      'time' => date('d/m/Y', $data['time']),
    );
  }

  function setClock($number) {
    $sql = 'update `pet_config` config_value = "'. $number .'" where config_name = "'. $this->prefix .'"';
    $this->db->query($sql);
  }

  function getClock() {
    $sql = 'select * from `pet_config` where config_name = "'. $this->prefix .'"';
    $query = $this->db->query($sql);
    
    if (empty($row = $query->fetch_assoc())) {
      $sql = 'insert into `pet_config` (lang, module, config_name, config_value) values("sys", "site", "'. $this->prefix .'", "0")';
      $this->db->query($sql);
      $row['config_value'] = 0;
    }
    return str_replace(',', '.', $row['config_value']);
  }
}
