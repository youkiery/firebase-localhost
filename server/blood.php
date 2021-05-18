<?php
class Blood extends Module {
  function __construct() {
    parent::__construct();
    $this->module = 'blood';
    $this->prefix = 'pet_' . $this->table .'_'. $this->module;
    $this->role = $this->getRole();
  }

  function getList() {
    global $filter;
    $data = array();

    $target = array();
    $sql = 'select * from `pet_' . $this->table . '_remind` where name = "blood" order by id';
    $query =$this->db->query($sql);
  
    while ($row = $query->fetch_assoc()) {
      $target[$row['id']] = $row['value'];
    }
  
    $sql = 'select * from ((select id, time, 0 as type from `' . $this->prefix . '_row`) union (select id, time, 1 as type from `' . $this->prefix . '_import`)) a order by time desc, id desc limit 20 offset '. ($filter['page'] - 1) * 20;
    $query =$this->db->query($sql);
    while ($row = $query->fetch_assoc()) {
      if ($row['type']) $sql = 'select * from `' . $this->prefix . '_import` where id = ' . $row['id'];
      else $sql = 'select * from `' . $this->prefix . '_row` where id = ' . $row['id'];
      $query2 =$this->db->query($sql);
      $row2 = $query2->fetch_assoc();
  
      $sql = 'select * from `pet_users` where userid = ' . $row2['doctor'];
      $user_query =$this->db->query($sql);
      $user = $user_query->fetch_assoc();
  
      $data []= array(
        'time' => date('d/m/y', $row2['time']),
        'id' => $row['id'],
        'typeid' => $row['type'],
        'doctor' => (!empty($user['first_name']) ? $user['first_name'] : ''),
        'type' => $row['type']
      );
      $len = count($data) - 1;

      if ($row['type']) {
        $data[$len]['target'] = 'Nhập ('. $row2['number1'] .'/'. $row2['number2'] .'/'. $row2['number3'] .') giá <span class="text-red">'. number_format($row2['price'], 0, '', ',') .' VND</span>';
        $data[$len]['number'] = '-';
        $data[$len]['number1'] = $row2['number1'];
        $data[$len]['number2'] = $row2['number2'];
        $data[$len]['number3'] = $row2['number3'];
      }
      else {
        $data[$len]['target'] = 'Xét nghiệm: '. (!empty($target[$row2['target']]) ? $target[$row2['target']] : '');
        $data[$len]['number'] = $row2['number'];
      } 
    } 
    return $data;
  }

  function initList() {
    global $filter;
    $data = array();

    $target = array();
    $sql = 'select * from `pet_' . $this->table . '_remind` where name = "blood" order by id';
    $query =$this->db->query($sql);
  
    while ($row = $query->fetch_assoc()) {
      $target[$row['id']] = $row['value'];
    }
  
    $sql = 'select * from ((select id, time, 0 as type from `' . $this->prefix . '_row`) union (select id, time, 1 as type from `' . $this->prefix . '_import`)) a order by time desc, id desc limit '. $filter['page'] * 20;
    $query =$this->db->query($sql);
    while ($row = $query->fetch_assoc()) {
      if ($row['type']) $sql = 'select * from `' . $this->prefix . '_import` where id = ' . $row['id'];
      else $sql = 'select * from `' . $this->prefix . '_row` where id = ' . $row['id'];
      $query2 =$this->db->query($sql);
      $row2 = $query2->fetch_assoc();
  
      $sql = 'select * from `pet_users` where userid = ' . $row2['doctor'];
      $user_query =$this->db->query($sql);
      $user = $user_query->fetch_assoc();
  
      $data []= array(
        'time' => date('d/m/y', $row2['time']),
        'id' => $row['id'],
        'typeid' => $row['type'],
        'doctor' => (!empty($user['first_name']) ? $user['first_name'] : ''),
        'type' => $row['type']
      );
      $len = count($data) - 1;

      if ($row['type']) {
        $data[$len]['target'] = 'Nhập ('. $row2['number1'] .'/'. $row2['number2'] .'/'. $row2['number3'] .') Giá: '. number_format($row2['price'], 0, '', ',') .' VND';
        $data[$len]['number'] = '-';
        $data[$len]['number1'] = $row2['number1'];
        $data[$len]['number2'] = $row2['number2'];
        $data[$len]['number3'] = $row2['number3'];
      }
      else {
        $data[$len]['target'] = 'Xét nghiệm: '. (!empty($target[$row2['target']]) ? $target[$row2['target']] : '');
        $data[$len]['number'] = $row2['number'];
      } 
    } 
    return $data;
  }

  function getCatalogById($id) {
    $sql = 'select * from `pet_'. $this->table .'_catalog` where id = ' . $id;
    $query = $this->db->query($sql);
    return $query->fetch_assoc();
  }

  function check_last_blood() {
    $sql = 'select * from `pet_config` where config_name = "'. $this->table .'_blood_number"';
    $query = $this->db->query($sql);
    if (!empty($row = $query->fetch_assoc())) {
      return $row['config_value'];
    }
    $sql = 'insert into `pet_config` (lang, module, config_name, config_value) values ("sys", "site", "'. $this->table .'_blood_number", "1")';
    $this->db->query($sql);
    return 0;
  }

  function check_blood_sample() {
    $sql = 'select * from `pet_config` where config_name like "'. $this->table .'_blood_sample%" order by config_name';
    $query = $this->db->query($sql);
    $number = array();
    $index = 1;
    while ($row = $query->fetch_assoc()) {
      $number[$index ++] = $row['config_value'];
    }
    return $number;
  }

  function update_blood_sample($data) {
    for ($i = 1; $i <= 3; $i++) {
      $sql = 'update `pet_config` set config_value = config_value + '. $data['number'. $i] .' where config_name = "'. $this->table .'_blood_sample_'. $i .'"';
      $this->db->query($sql);
    }
  }

  function check_blood_remind($name) {
    $targetid = 0;
    $sql = 'select * from `pet_' . $this->table . '_remind` where name = "blood" and value = "' . $name . '"';
    $query = $this->db->query($sql);
    if (!empty($row = $query->fetch_assoc())) {
      $targetid = $row['id'];
    } else {
      $sql = 'insert into `pet_' . $this->table . '_remind` (name, value) values ("blood", "' . $name . '")';
      if ($this->db->query($sql)) {
        $targetid = $this->db->insert_id;
      }
    }
    return $targetid;
  }

  
  function statistic($from, $end) {
    $total = array(
      'from' => date('d/m/Y', $from),
      'end' => date('d/m/Y', $end),
      'number' => 0,
      'sample' => 0,
      'total' => 0,
      'list' => array()
    );

    $doctor = $this->employ_list();
    $sql = 'select * from `' . $this->prefix . '_row` where (time between ' . $from . ' and ' . $end . ')';
    $query = $this->db->query($sql);
    $data = array();
    while ($row = $query->fetch_assoc()) {
      if (empty($data[$row['doctor']])) {
        $data[$row['doctor']] = array(
          'name' => $doctor[$row['doctor']],
          'number' => 0,
          'sample' => 0,
        );
      }
      $total['number']++;
      $total['sample'] += $row['number'];
      $total['chemist'] += ($row['start'] - $row['end']);
      $data[$row['doctor']]['number']++;
      $data[$row['doctor']]['sample'] += $row['number'];
    }

    $sql = 'select * from `' . $this->prefix . '_import` where (time between ' . $from . ' and ' . $end . ')';
    $query = $this->db->query($sql);
    $sum = 0;
    while ($row = $query->fetch_assoc()) {
      $sum += $row['price']; // tổng tiền nhập
    }
    foreach ($data as $row) {
      $total['list'] []= $row;
    }
    $total['total'] = number_format($sum * 1000, 0, '', ',') . ' VNĐ';
    return $total;
  }
}
