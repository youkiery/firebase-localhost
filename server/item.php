<?php

function expire() {
  global $data, $db, $result;

  $sql = "select * from pet_test_item where name = '$data->name'";
  if (empty($item = $db->fetch($sql))) {
    $sql = "insert into pet_test_item (name, code, shop, storage, catid, border, image) values('$data->name', '$data->code', 0, 0, 0, 10, '')";
    $item['id'] = $db->insertid($sql);
  }
  
  $data->expire = totime($data->expire);
  $sql = "insert into pet_test_item_expire (rid, number, expire, time) values($item[id], $data->number, $data->expire, ". time() .")";
  $db->query($sql);
  $result['status'] = 1;
  $result['messenger'] = 'Đã thêm hạn sử dụng';

  return $result;
}

function expire_done() {
  global $data, $db, $result;

  $sql = "delete from pet_test_item_expire where id = $data->id";
  $db->query($sql);
  
  $limit = time() * 60 * 60 * 24 * 60;
  $sql = "select a.id, b.name, a.expire from pet_test_item_expire a inner join pet_test_item b on a.rid = b.id where expire < $limit";
  $list = $db->all($sql);
  
  foreach ($list as $key => $value) {
    $list[$key]['expire'] = date('d/m/Y', $value['expire']);
  }
  
  $result['status'] = 1;
  $result['expired'] = getExpire();
  $result['list'] = $list;
  
  return $result;
}

function expired_init() {
  global $data, $db, $result;

  $limit = time() * 60 * 60 * 24 * 60;
  $sql = "select a.id, b.name, a.expire from pet_test_item_expire a inner join pet_test_item b on a.rid = b.id where expire < $limit";
  $list = $db->all($sql);
  
  foreach ($list as $key => $value) {
    $list[$key]['expire'] = date('d/m/Y', $value['expire']);
  }
  
  $result['status'] = 1;
  $result['list'] = $list;
  
  return $result;
}

function incat() {
  global $data, $db, $result;

  $sql = "select * from pet_test_item_cat where name = '$data->cat'";
  if (!empty($row = $db->fetch($sql))) $result['messenger'] = 'Danh mục đã tồn tại';
  else {
    $sql = "insert into pet_test_item_cat (name) values('$data->cat')";
    $result['status'] = 1;
    $result['cat'] = $db->insertid($sql);
    $result['catlist'] = getCatList();
  }

  return $result;
}

function init() {
  global $data, $db, $result;

  $userid = checkUserid();
  $result['status'] = 1;
  $result['purchase'] = getPurchase();
  $result['transfer'] = getTransfer();
  $result['expired'] = getExpire();
  $result['catlist'] = getCatList();
  $result['all'] = getSuggestList();
  $result['image'] = getImagePos();
  $result['list'] = getList();
  $result['user'] = getUserList();
  $result['cat'] = getUserCat($userid);
  $result['cats'] = getUserCats($userid);
  $result['usercat'] = getUserCatlist($userid);
  
  return $result;
}

function getUserCat($userid) {
  global $db;
  $sql = "select value from pet_test_config where module = 'itemcat' and name = $userid";
  return $db->arr($sql, 'value');
}

function getUserCats($userid) {
  global $db;
  $sql = "select b.name from pet_test_config a inner join pet_test_item_cat b on a.value = b.id where a.module = 'itemcat' and a.name = $userid";
  return implode(', ', $db->arr($sql, 'name'));
}

function getUserCatlist($userid) {
  global $db;
  $sql = "select b.* from pet_test_config a inner join pet_test_item_cat b on a.value = b.id where a.module = 'itemper' and a.name = $userid";
  return $db->all($sql);
}


function getUserList() {
  global $db;

  $sql = "select a.userid, b.name, b.username, b.fullname from pet_test_user_per a inner join pet_users b on a.userid = b.userid where a.module = 'item' and a.type > 0";
  $list = $db->all($sql);
  foreach ($list as $key => $row) {
    $sql = "select b.name from pet_test_config a inner join pet_test_item_cat b on a.value = b.id where a.module = 'itemper' and a.name = $row[userid]";
    $list[$key]['storage'] = implode(', ', $db->arr($sql, 'name'));
    $sql = "select b.id, b.name from pet_test_config a inner join pet_test_item_cat b on a.value = b.id where a.module = 'itemper' and a.name = $row[userid]";
    $list[$key]['per'] = $db->all($sql);
  }
  return $list;
}

function per() {
  global $data, $db, $result;

  if (count($data->cat)) {
    $cats = implode(', ', $data->cat);
    $sql = "delete from pet_test_config where module = 'itemper' and name = $data->userid and value not in ($cats)";
  }
  else $sql = "delete from pet_test_config where module = 'itemper' and name = $data->userid";
  $db->query($sql);
  foreach ($data->cat as $cat) {
    $sql = "select * from pet_test_config where module = 'itemper' and name = $data->userid and value = $cat";
    if (empty($db->fetch($sql))) {
      $sql = "insert into pet_test_config (module, name, value) values('itemper', $data->userid, $cat)";
      $db->query($sql);
    }
  }
  
  $sql = "select b.name from pet_test_config a inner join pet_test_item_cat b on a.value = b.id where a.module = 'itemper' and a.name = $data->userid";
  $result['storage'] = implode(', ', $db->arr($sql, 'name'));
  $sql = "select b.id, b.name from pet_test_config a inner join pet_test_item_cat b on a.value = b.id where a.module = 'itemper' and a.name = $data->userid";
  $result['per'] = $db->all($sql);

  $result['status'] = 1;
  return $result;
}

function inpos() {
  global $data, $db, $result;

  $image = '';
  foreach ($data->image as $value) {
    if (strlen($value) > 50) $image = $value;
  }
  
  $sql = "insert into pet_test_item_pos (name, image) values('$data->pos', '$image')";
  
  $result['status'] = 1;
  $result['id'] = $db->insertid($sql);
  $result['image'] = $image;

  return $result;
}

function inpositem() {
  global $data, $db, $result;

  foreach ($data->list as $key => $value) {
    $sql = "select * from pet_test_item_pos_item where posid = $data->posid and itemid = $value->id";
    if (empty($db->fetch($sql))) {
      $sql = "insert into pet_test_item_pos_item (posid, itemid) values($data->posid, $value->id)";
      $db->query($sql);
    }
  }
  
  $sql = "select b.id, a.name from pet_test_item a inner join pet_test_item_pos_item b on a.id = b.itemid where b.posid = $data->posid";
  $result['list'] = $db->all($sql);
  $result['status'] = 1;
  
  return $result;
}

function filter() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['list'] = getList();

  return $result;
}

function purchase_init() {
  global $data, $db, $result;

  $sql = "select name, storage + shop as number from pet_test_item where storage + shop < border order by name asc";
  $list = $db->all($sql);

  $result['status'] = 1;
  $result['list'] = $list;
  return $result;
}

function insert() {
  global $data, $db, $result;

  $name_sql = "select * from pet_test_item where name = '$data->name'";
  $code_sql = "select * from pet_test_item where code = '$data->code'";
  if (!empty($db->fetch($name_sql))) $result['messenger'] = 'Tên mặt hàng đã tồn tại'; 
  else if (!empty($db->fetch($code_sql))) $result['messenger'] = 'Mã mặt hàng đã tồn tại'; 
  else {
    $sql = "insert into pet_test_item (name, code, shop, storage, catid, border, image) values('$data->name', '$data->code', 0, 0, $data->cat, 10, '". implode(', ', $data->image) ."')";
    $db->query($sql);
  
    $result['status'] = 1;
    $result['list'] = getList();
  }
    
  return $result;
}

function position_init() {
  global $data, $db, $result;

  $sql = "select * from pet_test_item_pos order by name asc";
  $list = $db->all($sql);
  
  foreach ($list as $key => $value) {
    $sql = "select * from pet_test_item a inner join pet_test_item_pos_item b on a.id = b.itemid where b.posid = $value[id]";
    $list[$key]['position'] = $db->all($sql);
  }
  
  $result['status'] = 1;
  $result['list'] = $list;
      
  return $result;
}

function position_remove() {
  global $data, $db, $result;

  $sql = "delete from pet_test_item_pos_item where posid = $data->id";
  $db->query($sql);

  $sql = "delete from pet_test_item_pos where id = $data->id";
  $db->query($sql);
  
  $result['status'] = 1;
  return $result;
}

function remove() {
  global $data, $db, $result;

  $sql = "delete from pet_test_item where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  return $result;
}

function repos() {
  global $data, $db, $result;

  $sql = "delete from pet_test_item_pos_item where id = $data->itemid";
  $db->query($sql);
  
  $sql = "select b.id, a.name from pet_test_item a inner join pet_test_item_pos_item b on a.id = b.itemid where b.posid = $data->posid";
  $result['list'] = $db->all($sql);
  $result['status'] = 1;
  
  return $result;
}

function search() {
  global $data, $db, $result;

  $sql = "select name, code from pet_test_item where name like '%$data->key%'";
  
  $result['status'] = 1;
  $result['list'] = $db->all($sql);

  return $result;
}

function transfer_init() {
  global $data, $db, $result;

  $sql = "select name, storage, shop from pet_test_item where shop < border and storage > 0 order by name asc";
  $list = $db->all($sql);
  
  $result['status'] = 1;
  $result['list'] = $list;
  
  return $result;
}

function update() {
  global $data, $db, $result;

  $name_sql = "select * from pet_test_item where name = '$data->name' and id <> $data->id";
  $code_sql = "select * from pet_test_item where code = '$data->code' and id <> $data->id";
  if (!empty($db->fetch($name_sql))) $result['messenger'] = 'Tên mặt hàng đã tồn tại'; 
  else if (!empty($db->fetch($code_sql))) $result['messenger'] = 'Mã mặt hàng đã tồn tại'; 
  else {
    $sql = "update pet_test_item set catid = $data->cat, name = '$data->name', code = '$data->code', border = '$data->border', image = '". implode(', ', $data->image) ."' where id = $data->id";
    $db->query($sql);
  
    $result['status'] = 1;
    $result['data'] = getItemInfo($data->id);
  }
  
  return $result;
}

function getItemInfo($id) {
  global $db;

  $sql = "select * from pet_test_item where id = $id";
  $row = $db->fetch($sql);

  $row['alias'] = lower($row['name']);
  $row['image'] = explode(', ', $row['image']);
  $sql = "select a.id, a.name from pet_test_item_pos a inner join pet_test_item_pos_item b on a.id = b.posid where b.itemid = $id";
  $row['position'] = $db->all($sql);
  return $row;
}

function uppos() {
  global $data, $db, $result;
  $image = '';
  foreach ($data->image as $value) {
    if (strlen($value) > 50) $image = $value;
  }

  $sql = "update pet_test_item_pos set name = '$data->pos', image = '$image' where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;

  return $result;
}

function getList() {
  global $data, $db;
  
  /**
   * kiểm tra có cat không
   * nếu có, cập nhật csdl
   * nếu không, lấy từ csdl
   * kết quả xtra phải trừ bỏ không có trong config
   */
  
  $userid = checkUserid();
  // $check = true;
  // if (isset($data->cat) && !empty($data->cat)) {
  //   // lọc danh loại hàng từ config
  //   $cat = implode(', ', $data->cat);
  //   $sql = "select * from pet_test_config where module = 'itemper' and name = $userid and value in ($cat)";
  //   $cat = $db->arr($sql, 'value');
  //   if (count($cat)) {
  //     // có danh sách, hiển thị & cập nhật
  //     $check = false;
  //     $cats = implode(', ', $cat);
  //     $xtra = " a.catid in ($cats)";
  //     $sql = "update pet_test_config set value = '$cats'";
  //     $db->query($sql);
  //   }
  // }
  // if ($check) {
  //   // không có, lấy từ csdl
  //   $sql = "select * from pet_test_config where module = 'itemper' and name = $userid";
  //   $cat = $db->arr($sql, 'value');
  //   if (count($cat)) $xtra = ' a.catid in ('. implode(', ', $cat) .')';
  //   else $xtra = ' 0 ';
  // }

  $sql = "select a.*, b.name as cat from pet_test_item a inner join pet_test_item_cat b on a.catid = b.id order by a.name asc";
  $list = $db->all($sql);

  foreach ($list as $key => $value) {
    $list[$key]['alias'] = lower($value['name']);
    $list[$key]['image'] = explode(', ', $value['image']);

    $sql = "select a.id, a.name from pet_test_item_pos a inner join pet_test_item_pos_item b on a.id = b.posid where b.itemid = $value[id]";
    $list[$key]['position'] = $db->all($sql);
  }

  return $list;
}

function getPurchase() {
  global $data, $db;

  $sql = "select count(*) as number from pet_test_item where storage + shop < border";
  $number = $db->fetch($sql);
  return $number['number'];
}

function getTransfer() {
  global $data, $db;

  $sql = "select count(*) as number from pet_test_item where shop < border and storage > 0";
  $number = $db->fetch($sql);
  return $number['number'];
}

function getExpire() {
  global $data, $db;

  $limit = time() * 60 * 60 * 24 * 60;
  $sql = "select count(*) as number from pet_test_item_expire where expire < $limit";
  $number = $db->fetch($sql);
  return $number['number'];
}

function getCatList() {
  global $data, $db;
  
  $userid = checkUserid();
  $sql = "select * from pet_test_item_cat order by id asc";
  return $db->all($sql);
}

function getSuggestList() {
  global $data, $db;
  
  $sql = "select id, name from pet_test_item order by name asc";
  $list = $db->all($sql);
  foreach ($list as $key => $value) {
    $list[$key]['alias'] = lower($value['name']);
  }
  return $list;
}

function getImagePos() {
  global $data, $db;
  
  $sql = "select id, image from pet_test_item_pos order by name asc";
  return $db->obj($sql, 'id', 'image');
}

function excel() {
  global $data, $db, $result, $_FILES;

  $dir = str_replace('/server', '/', ROOTDIR);
  // $des = $dir ."export/DanhSachChiTietHoaDon_KV09102021-222822-523-1633793524.xlsx";

  $raw = $_FILES['file']['tmp_name'];
  $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
  $name = pathinfo($_FILES['file']['name'], PATHINFO_FILENAME);
  $file_name = $name . "-". time() . ".". $ext;
  $des = $dir ."export/$file_name";

  move_uploaded_file($raw, $des);

  $x = array();
  $xr = array(0 => 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ', 'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'HI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO');
  foreach ($xr as $key => $value) {
    $x[$value] = $key;
  }

  include $dir .'PHPExcel/IOFactory.php';
    
  $inputFileType = PHPExcel_IOFactory::identify($des);
  $objReader = PHPExcel_IOFactory::createReader($inputFileType);
  $objReader->setReadDataOnly(true);
  $objPHPExcel = $objReader->load($des);
  
  $sheet = $objPHPExcel->getSheet(0); 

  $highestRow = $sheet->getHighestRow(); 
  $highestColumn = $sheet->getHighestColumn();

  $sql = "select * from pet_test_doctor";
  $doctor = $db->obj($sql, 'name', 'userid');

  $sql = "select * from pet_test_type where active = 1";
  $type = $db->obj($sql, 'code', 'id');

  $sql = "select id, name from pet_test_config where module = 'usg'";
  $usg = $db->obj($sql, 'name', 'id');

  $col = array(
    'Mã hàng' => '', // 0
    'Bệnh viện' => '', // 2
    'Kho' => '', // 3
  );

  for ($j = 0; $j <= $x[$highestColumn]; $j ++) {
    $val = $sheet->getCell($xr[$j] . '1')->getValue();
    foreach ($col as $key => $value) {
      if ($key == $val) $col[$key] = $j;
    }
  }

  $exdata = array();
  for ($i = 2; $i <= $highestRow; $i ++) { 
    $temp = array();
    foreach ($col as $key => $j) {
      $val = $sheet->getCell($xr[$j] . $i)->getValue();
      $temp []= $val;
    }
    $exdata []= $temp;
  }

  $res = array(
    'on' => 1,
    'total' => 0, 'insert' => 0
  );
  $l = array();

  $sql = "select * from pet_test_item";
  $item = $db->all($sql);
  foreach ($exdata as $row) {
    foreach ($item as $i) {
      if ($i['code'] == $row[0]) {
        $l []= $row;
        break;
      }
    }
  }

  foreach ($l as $row) {
    $res['total'] ++;
    $sql = "update pet_test_item set shop = $row[1], storage = $row[2] where code = '$row[0]' limit";
    if ($db->query($sql)) $res['insert'] ++;
  }

  if (file_exists($des)) {
    unlink($des);
  }

  $result['messenger'] = "Đã chuyển dữ liệu Excel thành phiếu nhắc";
  $result['data'] = $res;
  return $result;
}
