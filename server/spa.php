<?php
class Spa extends Module {
  function __construct() {
    parent::__construct();
    $this->module = 'spa';
    $this->prefix = 'pet_' . $this->table .'_'. $this->module;
    $this->role = $this->getRole();
  }

  // tắm, tỉa, cắt móng, vệ sinh tai, vắt tuyến hôi, nhuộm lông, cắt lông bàn chân, cắt lông rối, vệ sinh răng miệng, combo
  function getList($time) {
    $list = array();
    
    $time = strtotime(date('Y/m/d', $time));
    $end = $time + 60 * 60 * 24 - 1;
    $sql = 'select id, customerid, note, type, done from `'. $this->prefix .'` where time between '. $time .' and '. $end;
    // die($sql);
    $query = $this->db->query($sql);

    $type = $this->getTypeObject();

    while ($row = $query->fetch_assoc()) {
      // echo $row['done'] . '<br>';
      $row['type'] = $this->parseType($row['type'], $type);
      $customer = $this->getCustonerId($row['customerid']);
      $row['name'] = $customer['name'];
      $row['phone'] = $customer['phone'];
      if ($row['done']) $row['time'] = date('H:i', $row['done']);
      else $row['time'] = 'Chưa xong';
      $list []= $row;
    } 

    return $list;
  }

  function parseType($string, $type) {
    $type_array = explode(',', $string);
    if (count($type_array)) {
      foreach ($type_array as $key => $value) {
        if ($value && !empty($type[$value])) $type_array[$key] = $type[$value];
        else unset($type_array[$key]);
      }
      $string = implode(', ', $type_array);
    }
    return $string;
  }

  function getTypeList() {
    $list = array();
    $sql = 'select * from `'. $this->prefix .'_type`';
    $query = $this->db->query($sql);

    while ($row = $query->fetch_assoc()) {
      $list []= array(
        'id' => $row['id'],
        'name' => $row['name'],
        'value' => 0
      );
    }
    return $list;
  }

  function getTypeObject() {
    $list = array();
    $sql = 'select * from `'. $this->prefix .'_type`';
    $query = $this->db->query($sql);

    while ($row = $query->fetch_assoc()) {
      $list [$row['id']]= $row['name'];
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
}
