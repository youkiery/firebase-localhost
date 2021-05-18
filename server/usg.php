<?php
class Usg extends Module {
  function __construct() {
    parent::__construct();
    $this->module = 'usg2';
    $this->prefix = 'pet_' . $this->table .'_'. $this->module;
    $this->role = $this->getRole();
  }

  function getList($filter) {
    $list = array();
    $data = array();

    $time = time();
    $limit = $time + 60 * 60 * 24 * 14;

    $sql = 'select a.id, c.name, c.phone, a.usgtime, a.expecttime, a.note from `'. $this->prefix .'` a inner join `pet_'. $this->table .'_pet` b on a.petid = b.id inner join `pet_'. $this->table .'_customer` c on b.customerid = c.id where (b.name like "%'. $filter['keyword'] .'%" or c.name like "%'. $filter['keyword'] .'%" or c.phone like "%'. $filter['keyword'] .'%") and expecttime < '. $limit .' and a.status = '. $filter['status'] .' order by a.expecttime desc '. ($filter['status'] > 0 ? 'limit 50' : '');
    $query = $this->db->query($sql);

    // tên thú cưng, sđt, vaccine, ngày tái chủng, ghi chú, trạng thại
    while ($row = $query->fetch_assoc()) {
      if ($time > $row['expecttime']) $row['color'] = 'red';
      else $row['color'] = 'green';
      $list []= $row;
    }

    usort($list, "cmp");

    // tên thú cưng, sđt, vaccine, ngày tái chủng, ghi chú, trạng thại
    foreach ($list as $row) {
      $data []= array(
        'id' => $row['id'],
        'name' => $row['name'],
        'number' => $row['phone'],
        'time' => date('d/m/Y', $row['usgtime']),
        'calltime' => date('d/m/Y', $row['expecttime']),
        'note' => $row['note'],
        'color' => $row['color'],
      );
    }
    return $data;
  }

//   function usgCurrentList($filter)
// {
//   switch ($filter['type']) {
//     case 2:
//       // danh sách đã sinh
//       return usgBirthList($filter);
//       break;
//     case 3:
//       // danh sách tiêm phòng
//       return usgVaccineList($filter);
//       break;
//     case 4:
//       // danh sách quản lý
//       // closed
//       break;
//     default:
//       // mạc định danh sách gần sinh
//       return usgRecallList($filter);
//   }
// }

// function usgRecallList()
// {
//   global $db, $module_name, $op, $vacconfigv2, $lang_module, $filter;

//   $status_list = array('Chưa gọi', 'Đã gọi');
//   $xtpl = new XTemplate("recall-list.tpl", PATH2);
//   $xtpl->assign('lang', $lang_module);
//   $index = 1;
//   $time = time() + $vacconfigv2['filter'];
//   $overtime = time();

//   $sql = 'select a.id, a.usgtime, a.expecttime, a.expectnumber, a.doctorid, b.id as petid, b.name as petname, c.name as customer, c.phone from `' . VAC_PREFIX . '_usg2` a inner join `' . VAC_PREFIX . '_pet` b on a.petid = b.id inner join `' . VAC_PREFIX . '_customer` c on b.customerid = c.id where expecttime < ' . $time . ' and a.status = ' . $filter['status'] . ' order by expecttime asc';
//   $query = $db->query($sql);

//   $status = $filter['status'];
//   $recall = array(0 => 'left', 'right');
//   while ($row = $query->fetch()) {
//     $xtpl->assign('index', $index++);
//     $xtpl->assign('id', $row['id']);
//     $xtpl->assign('customer', $row['customer']);
//     $xtpl->assign('phone', $row['phone']);
//     $xtpl->assign('expectnumber', $row['expectnumber']);
//     $xtpl->assign('expecttime', date('d/m/Y', $row['expecttime']));
//     if ($row['expecttime'] < $overtime) $xtpl->assign('bgcolor', 'orange');
//     else $xtpl->assign('bgcolor', '');
//     $xtpl->parse('main.row.' . $recall[$status]);
//     $xtpl->parse('main.row');
//   }
//   for ($i = 0; $i < 2; $i++) {
//     $filter['status'] = $i;
//     if ($status == $i) $xtpl->assign('recall_select', 'btn-info');
//     else $xtpl->assign('recall_select', 'btn-default');
//     $xtpl->assign('recall_link', '/' . $module_name . '/' . $op . '/?' . http_build_query($filter));
//     $xtpl->assign('recall_name', $status_list[$i]);
//     $xtpl->parse('main.button');
//   }
//   $xtpl->parse('main');
//   return $xtpl->text();
// }

// function usgBirthList($filter)
// {
//   global $db, $module_name, $op, $vacconfigv2;

//   $xtpl = new XTemplate("birth-list.tpl", PATH2);
//   $index = 1;
//   $time = time() + $vacconfigv2['filter'];
//   $overtime = time();

//   $sql = 'select a.id, a.usgtime, a.birthtime, a.number, b.id as petid, b.name as petname, c.name as customer, c.phone from `' . VAC_PREFIX . '_usg2` a inner join `' . VAC_PREFIX . '_pet` b on a.petid = b.id inner join `' . VAC_PREFIX . '_customer` c on b.customerid = c.id where birthtime < ' . $time . ' and a.status = 2 order by birthtime asc';
//   $query = $db->query($sql);

//   $recall = array(0 => 'left', 'right');
//   while ($row = $query->fetch()) {
//     $xtpl->assign('index', $index++);
//     $xtpl->assign('id', $row['id']);
//     $xtpl->assign('customer', $row['customer']);
//     $xtpl->assign('phone', $row['phone']);
//     $xtpl->assign('number', $row['number']);
//     $xtpl->assign('birthtime', date('d/m/Y', $row['birthtime']));
//     if ($row['birthtime'] < $overtime) $xtpl->assign('bgcolor', 'orange');
//     else $xtpl->assign('bgcolor', '');
//     $xtpl->parse('main.row');
//   }
//   $xtpl->parse('main');
//   return $xtpl->text();
// }

// function usgVaccineList()
// {
//   global $db, $module_name, $op, $vacconfigv2, $filter;

//   $xtpl = new XTemplate("vaccine-list.tpl", PATH2);
//   $index = 1;
//   $time = time() + $vacconfigv2['filter'];

//   $sql = 'select a.id, a.usgtime, a.vaccinetime, a.number, b.id as petid, b.name as petname, c.name as customer, c.phone from `' . VAC_PREFIX . '_usg2` a inner join `' . VAC_PREFIX . '_pet` b on a.petid = b.id inner join `' . VAC_PREFIX . '_customer` c on b.customerid = c.id where vaccinetime < ' . $time . ' and a.status = 3 order by vaccinetime asc';
//   $query = $db->query($sql);

//   $recall = array(0 => 'left', 'right');
//   while ($row = $query->fetch()) {
//     $xtpl->assign('index', $index++);
//     $xtpl->assign('id', $row['id']);
//     $xtpl->assign('customer', $row['customer']);
//     $xtpl->assign('phone', $row['phone']);
//     $xtpl->assign('number', $row['number']);
//     if ($row['vaccinetime']) $xtpl->assign('vaccinetime', date('d/m/Y', $row['vaccinetime']));
//     else $xtpl->assign('vaccinetime', 'Không tiêm phòng');
//     $xtpl->parse('main.row');
//   }
//   $xtpl->parse('main');
//   return $xtpl->text();
// }

  function getCustonerId($cid) {
    if (!empty($cid)) {
      $sql = 'select * from `pet_'. $this->table .'_customer` where id = ' . $cid;
      $query = $this->db->query($sql);
  
      if (!empty($row = $query->fetch_assoc())) return $row;
    }
    return array('phone' => '');
  }

  function getPetId($pid) {
    if (!empty($pid)) {
      $sql = 'select * from `pet_'. $this->table .'_pet` where id = ' . $pid;
      $query = $this->db->query($sql);
  
      if (!empty($row = $query->fetch_assoc())) return $row;
    }
    return array('customerid' => 0);
  }
}
