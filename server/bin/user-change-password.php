<?php
include_once(ROOTDIR .'/Encryption.php');

$crypt = new NukeViet\Core\Encryption($sitekey);

if (empty($_GET['old'])) $result['messenger'] = 'Mật khẩu cũ trống';
else if (empty($_GET['new'])) $result['messenger'] = 'Mật khẩu mới trống';
else {
    $old = $_GET['old'];
    $new = $_GET['new'];

    $sql = 'select * from `pet_users` where userid = '. $userid;
    $query = $mysqli->query($sql);
    $user_info = $query->fetch_assoc();

    if (empty($user_info)) $result['messenger'] = 'Người dùng không tồn tại';
    else if (!$crypt->validate_password($old, $user_info['password'])) $result['messenger'] = 'Sai mật khẩu cũ';
    else {
      $password = $crypt->hash_password($new, '{SSHA512}');
      $sql = 'update `pet_users` set password = "'. $password .'" where userid = '. $userid;
      $mysqli->query($sql);
      $result['status'] = 1;
      $result['messenger'] = 'Đã đổi mật khẩu';
    }
}

echo json_encode($result);
die();