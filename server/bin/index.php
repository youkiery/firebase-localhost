<?php

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");

$sitekey = 'e3e052c73ae5aa678141d0b3084b9da4';

include_once('../config.php');

$servername = $config['servername'];
$username = $config['username'];
$password = $config['password'];
$database = $config['database'];

// $servername = 'localhost';
// $username = 'petco339_test';
// $password = 'Ykpl.2412';
// $database = 'petco339_test';

define('INSERT_NOTIFY', 1);
define('EDIT_NOTIFY', 2);
define('COMPLETE_NOTIFY', 3);
define('REMOVE_NOTIFY', 4);
define('NV_ROOTDIR', pathinfo(str_replace(DIRECTORY_SEPARATOR, '/', __file__), PATHINFO_DIRNAME));
$branch = 'test';

$mysqli = new mysqli($servername, $username, $password, $database);
if ($mysqli->connect_errno) die('error: '. $mysqli -> connect_error);
$mysqli->set_charset('utf8');

// $sql = 'select * from pet_test_heal_medicine where limits like "%<br>%" or effect like "%<br>%"';
// $query = $mysqli->query($sql);

// while ($row = $query->fetch_assoc()) {
//     $sql = 'update pet_test_heal_medicine set limits = "'. str_replace('<br>', '\n', $row['limits']) .'", effect = "'. str_replace('<br>', '\n', $row['effect']) .'" where id = '. $row['id']; 
//     $mysqli->query($sql);
// }

if (isset($_GET['action']) && !empty($_GET['action'])) {
    $action = $_GET['action'];
    define('ROOTDIR', NV_ROOTDIR . '/');
    include_once(ROOTDIR . 'global_function.php');
    include_once(ROOTDIR . 'module.php');

    if (file_exists(ROOTDIR . $action . '.php')) {
        try {
            if ($action !== 'login' && $action !== 'version') {
                if (empty($_GET['userid'])) throw new Exception('no user');
                else {
                    $userid = $_GET['userid'];
                    $user = checkUserId($userid);
                    if (empty($user)) throw new Exception('no user exist');
                }
            }
            $result = array(
                'status' => 0,
                'messenger' => ''
            );
            date_default_timezone_set('asia/ho_chi_minh');
            include_once(ROOTDIR . $action . '.php');
            echo json_encode($result);
            die();
        }
        catch (Exception $e) {
            echo json_encode(array('status' => 0, 'messenge' => $e->getMessage()));
        }
        die();
    }
}

echo json_encode(array('status' => 0, 'messenge' => 'Lỗi chức năng'));
exit();
