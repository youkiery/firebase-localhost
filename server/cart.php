<?php
function auto() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['list'] = getlist();
  return $result;
}

function connect() {
  $servername = 'localhost';
  $username = 'root';
  $password = '';
  $database = 'watch';
  $db = new Database($servername, $username, $password, $database);
  return $db;
}

function pick() {
  global $data;
  
  $userid = checkUserid();
  $db = connect();

  $sql = "update wp_posts set post_status = 'wc-processing', post_content = '$userid' where id = $data->id";
  $db->query($sql);
  
  $result['status'] = 'Đã có người nhận';
  $result['list'] = getlist();
  return $result;
}

function done() {
  global $data;
  $db = connect();

  $sql = "update wp_posts set post_status = 'wc-completed' where id = $data->id";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['list'] = getlist();
  return $result;
}

function getlist() {
  global $data;
  $userid = checkUserid();
  $db = connect();
  $status = array(
    'wc-on-hold' => 'Chưa nhận',
    'wc-processing' => 'Đã có người nhận',
  );

  $sql = "select ID as id, post_status as status, post_date as time, post_excerpt as note from wp_posts where guid like '%shop%' and post_status <> 'wc-completed' and (post_content = '' or post_content = $userid)";
  $list = $db->all($sql);

  foreach ($list as $index => $data) {
    $sql = "SELECT * FROM `wp_postmeta` where post_id = $data[id]";
    $info = $db->object($sql, 'meta_key', 'meta_value');
    $list[$index]['method'] = $info['_payment_method_title'];
    $list[$index]['customer'] = $info['_billing_last_name'] . ' '. $info['_billing_first_name'];
    $list[$index]['address'] = $info['_billing_address_1'] . ', '. $info['_billing_city'];
    $list[$index]['mail'] = $info['_billing_email'];
    $list[$index]['phone'] = $info['_billing_phone'];
    $list[$index]['total'] = $info['_order_total'];
    $list[$index]['status'] = $status[$data['status']];
    
    $sql = "SELECT order_item_id as id, order_item_name as name FROM `wp_woocommerce_order_items` where order_id = $data[id]";
    $orders = $db->all($sql);

    foreach ($orders as $key => $order) {
      $sql = "SELECT * FROM `wp_woocommerce_order_itemmeta` where order_item_id = $order[id]";
      $item = $db->object($sql, 'meta_key', 'meta_value');
      
      $orders[$key]['price'] = $item['_line_total'] / $item['_qty'];
      $orders[$key]['number'] = $item['_qty'];
      $orders[$key]['total'] = $item['_line_total'];
    }
    
    $list[$index]['item'] = $orders;
  }

  return $list;
}

// function insert() {
//   global $data, $db, $result;

//   $sql = "select * from pet_test_customer where phone = '$data->phone'";
//   if (!empty($customer = $db->fetch($sql))) {
//     $sql = "update pet_test_customer set name = '$data->name' where id = $customer[id]";
//     $db->query($sql);
//   }
//   else {
//     $sql = "insert into pet_test_customer (name, phone, address) values ('$data->name', '$data->phone', '')";
//     $customer['id'] = $db->insertid($sql);
//   }

//   $data->cometime = totime($data->cometime);
//   $data->calltime = totime($data->calltime);
//   $userid = checkUserid();
//   $sql = "insert into pet_test_usg2 (customerid, userid, cometime, calltime, called, recall, number, status, note, time) values ($customer[id], $userid, $data->cometime, $data->calltime, 0, $data->calltime, $data->number, 0, '', ". time() .")";
//   $db->query($sql);
//   $result['status'] = 1;
//   $result['new'] = getlist(true);
//   $result['old'] = getOlder($customer['id']);
//   return $result;
// }

// function update() {
//   global $data, $db, $result;

//   $data->cometime = totime($data->cometime);
//   $data->calltime = totime($data->calltime);
//   $userid = checkUserid();
//   $sql = "update pet_test_usg2 set number = $data->number, cometime = $data->cometime, calltime = $data->calltime where id = $data->id";
//   // die($sql);
//   $db->query($sql);
//   $result['status'] = 1;
//   $result['list'] = getlist();
//   $result['new'] = getlist(true);
//   return $result;
// }

// function remove() {
//   global $data, $db, $result;

//   $sql = "delete from pet_test_usg2 where id = $data->id";
//   $db->query($sql);
//   $result['status'] = 1;
//   $result['new'] = getlist(true);
//   return $result;
// }

// function done() {
//   global $data, $db, $result;

//   $sql = "update pet_test_usg2 set status = 2, recall = ". time() ." where id = $data->id";
//   $db->query($sql);
//   $result['status'] = 1;
//   $result['list'] = getlist();
//   $result['old'] = getOlder($data->customerid);
//   return $result;
// }

// function called() {
//   global $data, $db, $result;

//   $sql = "update pet_test_usg2 set status = 1, note = '". $data->note ."', called = ". time() .", recall = ". (time() + 60 * 60 * 24 * 7) ." where id = $data->id";
//   $db->query($sql);
//   $result['status'] = 1;
//   $result['list'] = getlist();
//   return $result;
// }

// function uncalled() {
//   global $data, $db, $result;

//   $sql = "update pet_test_usg2 set status = 1, note = '". $data->note ."', called = ". time() .", recall = ". (time() + 60 * 60 * 24) ." where id = $data->id";
//   $db->query($sql);
//   $result['status'] = 1;
//   $result['list'] = getlist();
//   return $result;
// }

// function dead() {
//   global $data, $db, $result;

//   $sql = "update pet_test_usg2 set status = 2, recall = ". time() .", number = '". $data->number ."' where id = $data->id";
//   $db->query($sql);
//   $result['status'] = 1;
//   $result['list'] = getlist();
//   return $result;
// }

// function search() {
//   global $result;

//   $result['status'] = 1;
//   $result['list'] = getlist();
//   return $result;
// }


// function getOlder($customerid) {
//   global $db;

//   $sql = "select * from pet_test_usg2 where status < 2 and customerid = $customerid order by id asc";
//   $list = $db->all($sql);
//   $query = $db->query($sql);
//   foreach ($list as $index => $row) {
//     $list[$index]['number'] = $row['number'];
//     $list[$index]['calltime'] = date('d/m/Y', $row['calltime']);
//     $list[$index]['called'] = ($row['called'] ? date('d/m/Y', $row['called']) : '-');
//   }

//   return $list;
// }

// $url = "https://fcm.googleapis.com/fcm/send";
// $serverKey = 'AAAAvhUDQ-o:APA91bFmJIX-fIXf69yufz9u7I5xKxg3JyfZgdxe-0rqBfiCAhDXQLLVoAnkheIxpTICX9vCq3h9vPRHhXNyk4o0qXzd8QetX_3KJn_lR_pTjTbk38LT30M_yxXIdsd4OsraXKZr1xqD';
// $title = "Notification title";
// $body = "Hello I am from Your php server";
// $notification = array('title' =>$title , 'body' => $body, 'sound' => 'default', 'badge' => '1');
// $arrayToSend = array(
//   "condition" => "'tail' in topics",
//   'notification' => $notification,
//   'priority'=>'high'
// );
// $json = json_encode($arrayToSend);
// $headers = array();
// $headers[] = 'Content-Type: application/json';
// $headers[] = 'Authorization: key='. $serverKey;
// $ch = curl_init();
// curl_setopt($ch, CURLOPT_URL, $url);
// curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
// curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
// curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
// //Send the request
// $response = curl_exec($ch);
// //Close request
// if ($response === FALSE) die('FCM Send Error: ' . curl_error($ch));
// curl_close($ch);
