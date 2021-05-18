<?php
class Expire extends Module {
  function __construct() {
    parent::__construct();
    $this->module = 'expire';
    $this->prefix = 'pet_' . $this->table .'_'. $this->module;
    $this->role = $this->getRole();
  }

  // filter = timespan by second
  function getList($filter) {
    $time = time();
    $start = $time - $filter['ftime'];
    $end = $time + $filter['ftime'];
    $half = $time + $filter['ftime'] / 2;
    $sql = 'select a.*, b.name from `'. $this->prefix .'` a inner join `pet_'. $this->table .'_catalog` b on a.rid = b.id where (a.exp_time between '. $start .' and '. $end . ') and b.name like "%'. $filter['fname'] .'%" order by exp_time';
    $query = $this->db->query($sql);

    $list = array();
    while ($row = $query->fetch_assoc()) {
      $color = '';
      if ($row['exp_time'] < $time) $color = 'red';
      else if ($row['exp_time'] < $half) $color = 'orange';
      $list []= array(
        'id' => $row['id'],
        'name' => $row['name'],
        'color' => $color,
        'number' => $row['number'],
        'time' => date('d/m/Y', $row['exp_time'])
      );
    }
    return $list;
  }

  function getCatalogById($id) {
    $sql = 'select * from `pet_'. $this->table .'_catalog` where id = ' . $id;
    $query = $this->db->query($sql);
    return $query->fetch_assoc();
  }
}
