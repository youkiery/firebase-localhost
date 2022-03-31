<?php
class Database {
  private $db;
  public function __construct($servername, $username, $password, $database) {
    $this->db = new mysqli($servername, $username, $password, $database);
    if ($this->db->connect_errno) die('error: '. $this->db -> connect_error);
    $this->db->set_charset('utf8');
  }

  public function query($sql) {
    return $this->db->query($sql);
  }

  public function insertid($sql) {
    $this->db->query($sql);
    return $this->db->insert_id;
  }

  public function all($sql) {
    $list = array();
    $query = $this->db->query($sql);
    while ($row = $query->fetch_assoc()) $list []= $row;
    return $list;
  }

  public function obj($sql, $key, $name = '') {
    $list = array();
    $query = $this->db->query($sql);

    if (strlen($name)) {
      while ($row = $query->fetch_assoc()) $list [$row[$key]]= $row[$name];
    }
    else {
      while ($row = $query->fetch_assoc()) $list [$row[$key]]= $row;
    }
    return $list;
  }

  function arr($sql, $name) {
    $list = array();
    $query = $this->query($sql);
  
    while ($row = $query->fetch_assoc()) $list[]= $row[$name];
    return $list;
  }

  public function count($sql) {
    $query = $this->db->query($sql);
    $num = $query->num_rows;
    return $num;
  }

  public function fetch($sql) {
    $query = $this->db->query($sql);
    return $query->fetch_assoc();
  }
}
