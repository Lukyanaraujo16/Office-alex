<?php
date_default_timezone_set("America/Sao_Paulo");

require_once(__DIR__ . '/database.php');
// require_once(__DIR__ . '/vendor/autoload.php');
require_once(__DIR__ . '/libs/htmlputifier/HTMLPurifier.auto.php');

// define("MANAGEMENT_TOKEN", "g5UUCJBEj0BBmOAUHGvLUE5j0rsB8BRP");
define("OFFICE_VERSION", "3.8.35");
define("OFFICE_DEBUG", false);
define("ALLOWED_EMAILS", serialize(array("root@localhost.com", "hostmk2@gmail.com")));
ini_set("log_errors", 1);

$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

if (debugEnabled()) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    error_reporting(0);
    ini_set("display_errors", 0);
}

if (!defined('OFFICE_CONFIG')) {
    $DB_INFO = json_decode(file_get_contents(__DIR__ . "/../../dbinfo.json"), true);
    define("OFFICE_CONFIG", $DB_INFO);
}

class DB
{
    private $connection = NULL;
    private static $_instance = NULL;
    public static function getInstance()
    {
        if (!self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    private function __clone()
    {
    }
    public function getConnection($db_host, $db_port, $db_name, $db_user, $db_pass)
    {
        $con_name = $db_host . "_" . $db_name;
        try {
            if (!isset($this->connection[$con_name])) {
                $this->connection[$con_name] = new PDO("mysql:host=" . $db_host . ";port=" . $db_port . ";dbname=" . $db_name . ";charset=utf8", $db_user, $db_pass, array(PDO::ATTR_PERSISTENT => true, PDO::ATTR_TIMEOUT => 5));
                $this->connection[$con_name]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
        } catch (PDOException $e) {
            if (debugEnabled()) {
                exit("Failed to connect to DB: " . $e->getMessage());
            }
            return NULL;
        } catch (Exception $d) {
            if (debugEnabled()) {
                exit("Failed to connect to DB: " . $d->getMessage());
            }
            return NULL;
        }
        return $this->connection[$con_name];
    }
}
function getConnection()
{
    return DB::getInstance()->getConnection(OFFICE_CONFIG['remote_db']['hostname'], OFFICE_CONFIG['remote_db']['port'], OFFICE_CONFIG['remote_db']['database'], OFFICE_CONFIG['remote_db']['username'], OFFICE_CONFIG['remote_db']['password']);
}
function getOfficeConnection()
{
    return DB::getInstance()->getConnection(OFFICE_CONFIG['office_db']['hostname'], OFFICE_CONFIG['office_db']['port'], OFFICE_CONFIG['office_db']['database'], OFFICE_CONFIG['office_db']['username'], OFFICE_CONFIG['office_db']['password']);
}
function IsMariaDB()
{
    $link = mysqli_connect(OFFICE_CONFIG['remote_db']['hostname'], OFFICE_CONFIG['remote_db']['username'], OFFICE_CONFIG['remote_db']['password'], OFFICE_CONFIG['remote_db']['database'], OFFICE_CONFIG['remote_db']['port']);
    $info = mysqli_get_server_info($link);
    mysqli_close($link);
    return (mb_strpos($info, "MariaDB") !== false);
}
function startSession()
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}
function isLogged($destination = "/")
{
    startsession();
    if (!isset($_SESSION["__l0gg3d_us3r__"])) {
        header("Location: " . $destination);
        exit;
    }
}

function loginUser($username, $password)
{
    global $database;
    $database->where("username", $username);
    if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
        $result = $database->rawQueryOne('SELECT * from users where username = ? and (password = ? or password = ?)', array($username, cryptPassword($password, "xui"), cryptPassword($password, "xtreamcodes")));
    } else {
        $result = $database->rawQueryOne('SELECT * from reg_users where username = ? and (password = ? or password = ?)', array($username, cryptPassword($password, "xui"), cryptPassword($password, "xtreamcodes")));
    }

    if ($result) {
        if ($result["status"] == 1) {
            $allowed_groups = json_decode(getServerProperty("allowed_groups"), true);
            if (!in_array($result["member_group_id"], $allowed_groups)) {
                return 5;
            }
            updateLastLoginInfo($result['id']);
            startsession();
            $_SESSION["__l0gg3d_us3r__"] = $result["id"];
            return 1;
        }
        return 4;
    }
    return 3;
}

function loginAsReseller($user_id)
{
    startsession();
    $_SESSION["__l0gg3d_us3r__"] = $user_id;
    return true;
}

function updateLastLoginInfo($userid)
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        $userip = getIP();

        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "UPDATE `users` SET `last_login` = unix_timestamp(NOW()), `ip` = :user_ip  WHERE `id` = :user_id LIMIT 1;";
        } else {
            $sql = "UPDATE `reg_users` SET `last_login` = unix_timestamp(NOW()), `ip` = :user_ip  WHERE `id` = :user_id LIMIT 1;";
        }
        //$sql = "UPDATE `reg_users` SET `last_login` = unix_timestamp(NOW()), `ip` = :user_ip  WHERE `id` = :user_id LIMIT 1;";
        $database = $PDO->prepare($sql);
        $database->bindParam(":user_ip", $userip, PDO::PARAM_STR, 255);
        $database->bindParam(":user_id", $userid, PDO::PARAM_INT);
        if ($database->execute()) {
            return true;
        }
    }
    return false;
}

function logoutUser()
{
    startsession();
    unset($_SESSION);
    SESSION_DESTROY();
}
function getUserByID($userid)
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "SELECT * FROM `users` WHERE `id` = :userid LIMIT 1;";
        } else {
            $sql = "SELECT * FROM `reg_users` WHERE `id` = :userid LIMIT 1;";
        }
        $database = $PDO->prepare($sql);
        $database->execute([':userid' => intval($userid)]);
        return $database->fetch(PDO::FETCH_ASSOC);
    }
    return false;
}
function getLoggedUser()
{
    startsession();
    $user = getuserbyid($_SESSION["__l0gg3d_us3r__"]);
    if ($user) {
        return $user;
    }
    logoutuser();
    exit;
}
function getUserByUsername($username)
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "SELECT * FROM `users` WHERE `username` = :username LIMIT 1;";
        } else {
            $sql = "SELECT * FROM `reg_users` WHERE `username` = :username LIMIT 1;";
        }
        $database = $PDO->prepare($sql);
        $database->bindParam(":username", $username, PDO::PARAM_STR, 255);
        $database->execute();
        return $database->fetch(PDO::FETCH_ASSOC);
    }
    return false;
}
function getUserByEmail($email)
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "SELECT * FROM `users` WHERE `email` = :email LIMIT 1;";
        } else {
            $sql = "SELECT * FROM `reg_users` WHERE `email` = :email LIMIT 1;";
        }
        $database = $PDO->prepare($sql);
        $database->bindParam(":email", $email, PDO::PARAM_STR, 255);
        $database->execute();
        return $database->fetch(PDO::FETCH_ASSOC);
    }
    return false;
}
function getAllUsers($limit = null, $offset = null)
{
    $PDO = getConnection();
    if ($PDO !== null) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "SELECT * FROM `users` ";
        } else {
            $sql = "SELECT * FROM `reg_users` ";
        }
        if ($limit !== null && $offset !== null) {
            $sql .= "LIMIT :offset, :limit";
        }
        $database = $PDO->prepare($sql);
        if ($limit !== null && $offset !== null) {
            $database->bindParam(':limit', $limit, PDO::PARAM_INT);
            $database->bindParam(':offset', $offset, PDO::PARAM_INT);
        }
        $database->execute();
        return $database->fetchAll(PDO::FETCH_ASSOC);
    }
    return array();
}

function getAllUsersCount()
{
    global $database;
    if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
        $count = $database->getValue("users", "count(*)");
    } else {
        $count = $database->getValue("reg_users", "count(*)");
    }

    return $count;
}


function updateUser($user_id, $username, $password, $email, $member_group_id, $notes)
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "UPDATE `users` SET `username` = :username, `password` = :password, `email` = :email, `member_group_id` = :member_group_id, `notes` = :notes WHERE `id` = :user_id LIMIT 1;";
            if (empty($password)) {
                $sql = "UPDATE `users` SET `username` = :username, `email` = :email, `member_group_id` = :member_group_id, `notes` = :notes WHERE `id` = :user_id LIMIT 1;";
            }
            $database = $PDO->prepare($sql);
            $database->bindParam(":username", $username, PDO::PARAM_STR, 255);
            if (!empty($password)) {
                $password = cryptPassword($password, "xui");
                $database->bindParam(":password", $password, PDO::PARAM_STR, 255);
            }
        } else {

            $sql = "UPDATE `reg_users` SET `username` = :username, `password` = :password, `email` = :email, `member_group_id` = :member_group_id, `notes` = :notes WHERE `id` = :user_id LIMIT 1;";
            if (empty($password)) {
                $sql = "UPDATE `reg_users` SET `username` = :username, `email` = :email, `member_group_id` = :member_group_id, `notes` = :notes WHERE `id` = :user_id LIMIT 1;";
            }
            $database = $PDO->prepare($sql);
            $database->bindParam(":username", $username, PDO::PARAM_STR, 255);
            if (!empty($password)) {
                $password = cryptPassword($password, "xtreamcodes");
                $database->bindParam(":password", $password, PDO::PARAM_STR, 255);
            }
        }
        $database->bindParam(":email", $email, PDO::PARAM_STR, 255);
        $database->bindParam(":member_group_id", $member_group_id, PDO::PARAM_INT);
        $database->bindParam(":notes", $notes, PDO::PARAM_STR);
        $database->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        if ($database->execute()) {
            return true;
        }
    }
    return false;
}

function getIP()
{
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    } else if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}
function deleteExpiredTestUsersByOwner($owner_id, $remove_expired, $remove_test, $start_date, $end_date)
{
    if (!$remove_expired && !$remove_test) {
        return false;
    }
    $PDO = getconnection();
    if ($PDO !== NULL) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "DELETE FROM `lines` WHERE `member_id` = :owner_id AND `created_at` >= :start_date AND `created_at` <= :end_date";
        } else {
            $sql = "DELETE FROM `users` WHERE `member_id` = :owner_id AND `created_at` >= :start_date AND `created_at` <= :end_date";
        }
        if ($remove_expired) {
            $sql .= " AND unix_timestamp(NOW()) > `exp_date`";
        }
        if ($remove_test) {
            $sql .= " AND `is_trial` = 1";
        }
        $database = $PDO->prepare($sql);
        $database->bindParam(":owner_id", $owner_id, PDO::PARAM_INT);
        $database->bindParam(":start_date", $start_date, PDO::PARAM_INT);
        $database->bindParam(":end_date", $end_date, PDO::PARAM_INT);
        if ($database->execute()) {
            return true;
        }
    }
    return false;
}

function transferResellers($resellers, $new_owner, $new_group)
{
    $PDO = getconnection();
    foreach ($resellers as $reseller_id) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "UPDATE `users` SET `owner_id` = :owner_id WHERE `id` = :user_id LIMIT 1;";
            if ($new_group) {
                $sql = "UPDATE `users` SET `member_group_id` = :member_group_id, `owner_id` = :owner_id WHERE `id` = :user_id LIMIT 1;";
            }
        } else {
            $sql = "UPDATE `reg_users` SET `owner_id` = :owner_id WHERE `id` = :user_id LIMIT 1;";
            if ($new_group) {
                $sql = "UPDATE `reg_users` SET `member_group_id` = :member_group_id, `owner_id` = :owner_id WHERE `id` = :user_id LIMIT 1;";
            }
        }
        $database = $PDO->prepare($sql);
        $database->bindParam(":owner_id", $new_owner, PDO::PARAM_INT);
        if ($new_group) {
            $database->bindParam(":member_group_id", $new_group, PDO::PARAM_INT);
        }
        $database->bindParam(":user_id", $reseller_id, PDO::PARAM_INT);
        $database->execute();
    }
    return true;
}
function updateUserPassword($user_id, $password)
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "UPDATE `users` SET `password` = :password WHERE `id` = :user_id LIMIT 1;";
            $password = cryptPassword($password, "xui");
        } else {
            $sql = "UPDATE `reg_users` SET `password` = :password WHERE `id` = :user_id LIMIT 1;";
            $password = cryptPassword($password, "xtreamcodes");
        }

        $database = $PDO->prepare($sql);
        $database->bindParam(":password", $password, PDO::PARAM_STR, 255);
        $database->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        if ($database->execute()) {
            return true;
        }
    }
    return false;
}

function insertTest($email, $ip, $user_agent, $type = "iptv")
{
    $PDO = getofficeconnection();
    if ($PDO !== NULL) {
        $sql = "INSERT INTO `test_historic` (`id`, `email`, `ip`, `user_agent`, `type`) VALUES (NULL, :email, :ip, :user_agent, :type);";
        $database = $PDO->prepare($sql);
        $database->bindParam(":email", $email, PDO::PARAM_STR, 255);
        $database->bindParam(":ip", $ip, PDO::PARAM_STR, 255);
        $database->bindParam(":user_agent", $user_agent, PDO::PARAM_STR, 255);
        $database->bindParam(":type", $type, PDO::PARAM_STR, 255);
        if ($database->execute()) {
            return true;
        }
    }
    return false;
}

function existTest($email)
{
    if (mb_strpos($email, '+') !== false) {
        return false;
    }
    $PDO = getofficeconnection();
    if ($PDO !== NULL) {
        $sql = "SELECT `id` FROM `test_historic` WHERE `email` LIKE :email;";
        $database = $PDO->prepare($sql);
        $email_ = "%" . $email . "%";
        $database->bindParam(":email", $email_, PDO::PARAM_STR, 255);
        $database->execute();
        if (0 < $database->rowCount()) {
            return true;
        }
    }
    return false;
}

function existTestIP($ip)
{
    $PDO = getofficeconnection();
    if ($PDO !== NULL) {
        $sql = "SELECT `ip` FROM `test_historic` WHERE `ip` LIKE :ip;";
        $database = $PDO->prepare($sql);
        $database->bindParam(":ip", $ip, PDO::PARAM_STR, 255);
        $database->execute();
        if (10 < $database->rowCount()) {
            return true;
        }
    }
    return false;
}

function createFastTest($owner_id, $package_id, $type = "")
{
    if ($type == "binstream") {
        include_once(__DIR__ . "/class/binstream.php");
        $binstream = new BinStream();
        $binstream_allowed_packages = json_decode(getServerProperty('binstream_allowed_packages'), true);
        if (!in_array($package_id, $binstream_allowed_packages)) {
            return false;
        }
        $owner = getUserByID($owner_id);
        $user_length = getServerProperty('binstream_user_length', 8);
        $user_char = getServerProperty('binstream_user_char', '1');
        $test_time = getServerProperty('binstream_test_time', 4);
        $duration = ($test_time / 24);
        $username = CodeGenerator($user_length, $user_char);
        $password = CodeGenerator($user_length, $user_char);

        $data = [
            'name' => OFFICE_CONFIG['panel_id'],
            'email' => $username . "@" . OFFICE_CONFIG['binstream']['email'],
            'password' => $password,
            'status' => -1,
            'type' => 0,
            'serviceTag' => "",
            'servicePeriod' => $duration,
            'productId' => $package_id,
            'exField1' => $owner['username'],
            'exField2' => $owner['id'],
            'exField3' => $password,
            'exField4' => json_encode(['email' => '', 'phone' => ''])
        ];
        $new_test = $binstream->create($data);
        if ($new_test['id']) {
            insertRegUserLog($owner_id, $username, $password, '<b>Novo Teste Binstream</b> | Pacote: ' . $package_id . ' | Créditos: <font color="green">' . $owner['credits'] . '</font> > <font color="red">' . $owner['credits'] . '</font> | Custo: 0 Crédito');
            return $new_test['id'];
        }
    } else {
        $package = getPackageByID($package_id);
        if ($package && $package["is_trial"]) {
            $user_length = getServerProperty('iptv_code_size', 8);
            $user_char = getServerProperty('iptv_code_characters', '1');
            $username = CodeGenerator($user_length, $user_char);
            $password = CodeGenerator($user_length, $user_char);
            $duration = $package["trial_duration"] . " " . $package["trial_duration_in"];
            $email = "";
            $phone = "";
            $result = createClient($owner_id, $username, $password, $phone, $email, $duration, $package["bouquets"], "", 1);
            if ($result) {
                $reseller = getuserbyid($owner_id);
                if ($reseller) {
                    insertRegUserLog($owner_id, $username, $password, '<b>Novo Teste</b> | Pacote: ' . $package['package_name'] . ' | Crditos: <font color="green">' . $reseller['credits'] . '</font> > <font color="red">' . $reseller['credits'] . '</font> | Custo: 0 Crédito');
                }
                return $result;
            }
        }
    }
    return false;
}

function createFastTestDash($owner_id, $type, $notes = "")
{
    if ($type == "binstream") {
        $owner = getUserByID($owner_id);
        $user_length = getServerProperty('binstream_user_length', 8);
        $user_char = getServerProperty('binstream_user_char', '1');
        $username = CodeGenerator($user_length, $user_char);
        $password = CodeGenerator($user_length, $user_char);
        $package_id = getServerProperty("binstream_fast_test_package");
        $test_time = getServerProperty('binstream_test_time', 4);
        $duration = ($test_time / 24);

        $data = [
            'name' => OFFICE_CONFIG['panel_id'],
            'email' => $username . "@" . OFFICE_CONFIG['binstream']['email'],
            'password' => $password,
            'status' => -1,
            'type' => 0,
            'serviceTag' => $notes,
            'servicePeriod' => $duration,
            'productId' => $package_id,
            'exField1' => $owner['username'],
            'exField2' => $owner['id'],
            'exField3' => $password,
            'exField4' => json_encode(['email' => '', 'phone' => ''])
        ];
        include_once(__DIR__ . "/class/binstream.php");
        $binstream = new BinStream();
        $new_test = $binstream->create($data);
        if ($new_test['id']) {
            insertRegUserLog($owner_id, $username, $password, '<b>Novo Teste Binstream (Dash)</b> | Pacote: ' . $package_id . ' | Créditos: <font color="green">' . $owner['credits'] . '</font> > <font color="red">' . $owner['credits'] . '</font> | Custo: 0 Crédito');
            return ["username" => $username, "password" => $password, "duration" => $test_time . " horas"];
        }
    } elseif ($type == "iptv") {
        $package = getPackageByID(getServerProperty("fast_test_package"));
        if ($package && $package["is_trial"]) {
            $user_length = getServerProperty('iptv_code_size', 8);
            $user_char = getServerProperty('iptv_code_characters', '1');
            $username = CodeGenerator($user_length, $user_char);
            $password = CodeGenerator($user_length, $user_char);

            $exp_date = strtotime("+" . $package["trial_duration"] . " " . $package["trial_duration_in"]);

            if (insertClient($owner_id, $username, $password, "", "", $exp_date, "", $notes, $package["bouquets"], 1, 1)) {
                $reseller = getuserbyid($owner_id);
                if ($reseller) {
                    insertRegUserLog($owner_id, $username, $password, '<b>Novo Teste IPTV (Dash)</b> | Pacote: ' . $package['package_name'] . ' | Créditos: <font color="green">' . $reseller['credits'] . '</font> > <font color="red">' . $reseller['credits'] . '</font> | Custo: 0 Crédito');
                }
                return ["username" => $username, "password" => $password, "duration" => $exp_date];
            }
        }
    } elseif ($type == "code") {
        $package = getPackageByID(getServerProperty("code_fast_test_package"));
        if ($package && $package["is_trial"]) {
            $user_length = getServerProperty('code_user_length', 8);
            $user_char = getServerProperty('code_user_char', '1');
            $username = CodeGenerator($user_length, $user_char);
            $password = getServerProperty("code_default_pass", "VeryStonksP2P");
            $password = empty($password) ? "VeryStonksP2P" : $password;

            if ($package["trial_duration_in"] == "hours") {
                $duration = $package["trial_duration"] . " horas";
            } elseif ($package["trial_duration_in"] == "days") {
                $duration = $package["trial_duration"] . " dias";
            } elseif ($package["trial_duration_in"] == "months") {
                $duration = $package["trial_duration"] . " meses";
            }

            $exp_date = strtotime("+" . $package["trial_duration"] . " " . $package["trial_duration_in"]);

            if (insertClient($owner_id, $username, $password, "", "", $exp_date, "", $notes, $package["bouquets"], 1, 1)) {
                $reseller = getuserbyid($owner_id);
                if ($reseller) {
                    insertRegUserLog($owner_id, $username, $password, '<b>Novo Teste Código (Dash)</b> | Pacote: ' . $package['package_name'] . ' | Créditos: <font color="green">' . $reseller['credits'] . '</font> > <font color="red">' . $reseller['credits'] . '</font> | Custo: 0 Crédito');
                }
                return ["username" => $username, "password" => $password, "duration" => $duration];
            }
        }
    }
    return false;
}

function createClient($owner_id, $username, $password, $phone, $email, $duration = "2 hours", $bouquet, $reseller_notes, $is_trial = 0)
{
    $exp_date = strtotime("+" . $duration);
    return insertClient($owner_id, $username, $password, $phone, $email, $exp_date, "", $reseller_notes, $bouquet, 1, $is_trial);
}

function createP2P($owner_id, $username, $password, $phone, $email, $duration = "2 hours", $bouquet, $reseller_notes, $is_trial = 0)
{
    $exp_date = strtotime("+" . $duration);
    return insertClient($owner_id, $username, $password, $phone, $email, $exp_date, "Code By Office", $reseller_notes, $bouquet, 1, $is_trial);
}

function insertClient($owner_id, $username, $password, $phone, $email, $exp_date, $admin_notes, $reseller_notes, $bouquet, $max_connections, $is_trial)
{
    if (existClient($username)) {
        return false;
    }
    $PDO = getconnection();
    if ($PDO !== NULL) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "INSERT INTO `lines` (`id`, `member_id`, `username`, `password`, `phone`, `email`, `exp_date`, `admin_notes`, `reseller_notes`, `bouquet`,`allowed_outputs`, `max_connections`, `is_trial`, `created_at`) VALUES \r\n            (NULL, :owner_id, :username, :password, :phone, :email, :exp_date, :admin_notes, :reseller_notes, :bouquet, '[1,2]', :max_connections, :is_trial, unix_timestamp(NOW()));";
        } else {
            $sql = "INSERT INTO `users` (`id`, `member_id`, `username`, `password`, `phone`, `email`, `exp_date`, `admin_notes`, `reseller_notes`, `bouquet`, `max_connections`, `is_trial`, `created_at`, `created_by`) VALUES \r\n            (NULL, :owner_id, :username, :password, :phone, :email, :exp_date, :admin_notes, :reseller_notes, :bouquet, :max_connections, :is_trial, unix_timestamp(NOW()), :owner_id);";
        }
        $database = $PDO->prepare($sql);
        $database->bindParam(":owner_id", $owner_id, PDO::PARAM_INT);
        $database->bindParam(":username", $username, PDO::PARAM_STR, 255);
        $database->bindParam(":password", $password, PDO::PARAM_STR, 255);
        $database->bindParam(":phone", $phone, PDO::PARAM_STR, 255);
        $database->bindParam(":email", $email, PDO::PARAM_STR, 255);
        $database->bindParam(":exp_date", $exp_date, PDO::PARAM_INT);
        $database->bindParam(":admin_notes", $admin_notes, PDO::PARAM_STR, 500);
        $database->bindParam(":reseller_notes", $reseller_notes, PDO::PARAM_STR, 500);
        $database->bindParam(":bouquet", $bouquet, PDO::PARAM_STR);
        $database->bindParam(":max_connections", $max_connections, PDO::PARAM_INT);
        $database->bindParam(":is_trial", $is_trial, PDO::PARAM_INT);
        if ($database->execute()) {
            $user_id = $PDO->lastInsertId();
            if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
                return $user_id;
            } else {
                $sql = "INSERT INTO `user_output` (`id`, `user_id`, `access_output_id`) VALUES (NULL, :userid1, '1'), (NULL, :userid2, '2')";
                $database = $PDO->prepare($sql);
                $database->bindParam(":userid1", $user_id, PDO::PARAM_INT);
                $database->bindParam(":userid2", $user_id, PDO::PARAM_INT);
                if ($database->execute()) {
                    return $user_id;
                }
            }
        }
    }
    return false;
}

function insertMultiCodes($owner_id, $exp_date, $admin_notes, $reseller_notes, $package_id, $max_connections, $is_trial, $amount)
{
    $PDO = getconnection();
    $password = getServerProperty("code_default_pass");
    $package = getPackageByID($package_id);
    $bouquet = $package["bouquets"];
    $user_list = [];

    if ($PDO !== NULL) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "INSERT INTO `lines` (`id`, `member_id`, `username`, `password`, `exp_date`, `admin_notes`, `reseller_notes`, `bouquet`, `allowed_outputs`, `max_connections`, `is_trial`, `created_at`) VALUES ";
        } else {
            $sql = "INSERT INTO `users` (`id`, `member_id`, `username`, `password`, `exp_date`, `admin_notes`, `reseller_notes`, `bouquet`, `max_connections`, `is_trial`, `created_at`, `created_by`) VALUES ";
        }
        foreach (range(1, $amount) as $i) {
            $username = (string) random_int(10000000, 99999999);
            $user_list[] = $username;
            if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
                $sql .= "(NULL, {$owner_id}, '{$username}', '{$password}', {$exp_date}, '{$admin_notes}', '{$reseller_notes}', '{$bouquet}', '[1,2]', {$max_connections}, {$is_trial}, " . time() . "), ";
            } else {
                $sql .= "(NULL, {$owner_id}, '{$username}', '{$password}', {$exp_date}, '{$admin_notes}', '{$reseller_notes}', '{$bouquet}', {$max_connections}, {$is_trial}, " . time() . " , {$owner_id}), ";
            }
        }

        $sql = rtrim(rtrim($sql), ",");

        $database = $PDO->prepare($sql);
        $result1 = $database->execute();

        if ($result1) {
            if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
                return $user_list;
            } else {
                $lastinsert = json_decode(getNewCodes($amount), true);
                $sql2 = "INSERT INTO `user_output` (`id`, `user_id`, `access_output_id`) VALUES ";
                foreach ($lastinsert as $value) {
                    $sql2 .= "(NULL, {$value}, '1'),  (NULL, {$value}, '2'),";
                }
                $sql2 = rtrim(rtrim($sql2), ",");
                $database = $PDO->prepare($sql2);
                $result2 = $database->execute();

                if ($result2) {
                    return $user_list;
                }
                return false;
            }
        }
        return false;
    }
    return false;
}

function getNewCodes($limit = 10)
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "SELECT `id` FROM `lines` ORDER BY `id` DESC LIMIT :_limit;";
        } else {
            $sql = "SELECT `id` FROM `users` ORDER BY `id` DESC LIMIT :_limit;";
        }
        $database = $PDO->prepare($sql);
        $database->bindParam(":_limit", $limit, PDO::PARAM_INT);
        if ($database->execute()) {
            return json_encode($database->fetchAll(PDO::FETCH_COLUMN));
        }
    }
    return false;
}

function existClient($username)
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "SELECT `id` FROM `lines` WHERE `username` = :username;";
        } else {
            $sql = "SELECT `id` FROM `users` WHERE `username` = :username;";
        }
        $database = $PDO->prepare($sql);
        $database->bindParam(":username", $username, PDO::PARAM_STR, 255);
        $database->execute();
        if (0 < $database->rowCount()) {
            return true;
        }
    }
    return false;
}
function getAllClients($p2p = false, $expiring = false)
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        $password = getServerProperty("code_default_pass");
        $code_status = getServerProperty("code_status");
        if ($code_status == "1") {
            $where_iptv = "WHERE `password` != :password";
            $where_code = "WHERE `password` = :password";
        } else {
            $where_iptv = "";
            $where_code = "WHERE `password` = :password";
        }
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            if ($p2p) {
                $sql = "SELECT * FROM `lines` " . $where_code . " ORDER BY `id` DESC;";
            } elseif ($expiring) {
                $now = strtotime("now");
                $yesterday = $now - 172800;
                $nextweek = $now + 604800;
                $sql = "SELECT * FROM `lines` WHERE `exp_date` > " . $yesterday . " and `exp_date` < " . $nextweek . " ORDER BY `exp_date` DESC;";
            } else {
                $sql = "SELECT * FROM `lines` " . $where_iptv . " ORDER BY `id` DESC;";
            }
        } else {
            if ($p2p) {
                $sql = "SELECT * FROM `users` " . $where_code . " ORDER BY `id` DESC;";
            } elseif ($expiring) {
                $now = strtotime("now");
                $yesterday = $now - 172800;
                $nextweek = $now + 604800;
                $sql = "SELECT * FROM `users` WHERE `exp_date` > " . $yesterday . " and `exp_date` < " . $nextweek . " ORDER BY `exp_date` DESC;";
            } else {
                $sql = "SELECT * FROM `users` " . $where_iptv . " ORDER BY `id` DESC;";
            }
        }

        $database = $PDO->prepare($sql);
        if ($code_status == "1") {
            $database->bindParam(":password", $password, PDO::PARAM_STR, 255);
        }
        $database->execute();
        return $database->fetchAll(PDO::FETCH_ASSOC);
    }
    return array();
}

function getClientByID($client_id, $type = "")
{
    if ($type == "binstream") {
        include_once(__DIR__ . "/class/binstream.php");
        $binstream = new BinStream();
        $result = $binstream->getuser($client_id);
        if ($result['id'] == $client_id) {
            return $result;
        }
    } else {
        $PDO = getconnection();
        if ($PDO !== NULL) {
            if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
                $sql = "SELECT * FROM `lines` WHERE `id` = :client_id LIMIT 1;";
            } else {
                $sql = "SELECT * FROM `users` WHERE `id` = :client_id LIMIT 1;";
            }
            $database = $PDO->prepare($sql);
            $database->bindParam(":client_id", $client_id, PDO::PARAM_INT);
            $database->execute();
            return $database->fetch(PDO::FETCH_ASSOC);
        }
    }

    return false;
}

function getAllClientsAdmin($p2p = false, $expiring = false)
{
    $results = array();
    $all_clients = getallclients($p2p, $expiring);
    $all_users = getallusers();
    foreach ($all_clients as $current_client) {
        $reseller_key = array_search($current_client["member_id"], array_column($all_users, "id"));
        $reseller_name = $reseller_key !== false ? $all_users[$reseller_key]["username"] : "Desconhecido!";
        $current_client["reseller_name"] = $reseller_name;
        $results[] = $current_client;
    }
    return $results;
}
// function getAllClientsAdminWithOptions($start = 0, $length = 10, $columns = array(), $search_value = "", $order_column_index = NULL, $order_type = "asc", $p2p = false, $expiring = false)
// {
//     $result = array("data" => array(), "recordsTotal" => 0, "recordsFiltered" => 0);
//     $all_clients = dataOutput($columns, getallclientsadmin($p2p, $expiring));
//     if ($order_column_index !== NULL && isset($columns[$order_column_index]["db"])) {
//         $order_column = $columns[$order_column_index]["db"];
//         usort($all_clients, function ($a, $b) use ($order_column, $order_type) {
//             if ($a[$order_column] === $b[$order_column]) {
//                 return 0;
//             }
//             if ($order_type == "asc") {
//                 return strip_tags($b[$order_column]) < strip_tags($a[$order_column]) ? 1 : -1;
//             }
//             return strip_tags($a[$order_column]) < strip_tags($b[$order_column]) ? 1 : -1;
//         });
//     }
//     $current_index = 0;
//     foreach ($all_clients as $current_client) {
//         if (tryFind($current_client, $columns, $search_value)) {
//             if ($start <= $current_index && count($result["data"]) < $length) {
//                 $result["data"][] = $current_client;
//             }
//             $current_index++;
//         }
//     }
//     $result["recordsTotal"] = count($all_clients);
//     $result["recordsFiltered"] = $current_index;
//     return $result;
// }

function getAllClientsTable($owner_id, $start = 1, $length = 10, $search_value = "", $order_column_index = NULL, $order_type = "asc", $code = false, $expiring = false, $status = NULL, $type = NULL, $reseller_id = NULL)
{
    $owner = getuserbyid($owner_id);
    global $database;
    $result = array("data" => array(), "recordsTotal" => 0, "recordsFiltered" => 0);

    $result["recordsTotal"] = getClientsCount(getLoggedUser(), $status, $type, $reseller_id, $code);
    $result["recordsFiltered"] = $result["recordsTotal"];

    if ($status !== null && $status !== "") {
        if ($status == "enabled") {
            $database->where("(`admin_enabled` = 1 AND `enabled` = 1 AND (`exp_date` > ? OR `exp_date` IS NULL))", array(time()));
        } elseif ($status == "disabled") {
            $database->where("(admin_enabled = 0 OR enabled = 0)");
        } elseif ($status == "expired") {
            $database->where("((`exp_date` < ?) AND (`exp_date` IS NOT NULL) AND (`admin_enabled` = 1) AND (`enabled` = 1))", array(time()));
        }
    }

    if ($type !== NULL && $type !== "") {
        if ($type == "official") {
            $database->where("is_trial", 0);
        } elseif ($type == "trial") {
            $database->where("is_trial", 1);
        } elseif ($type == "restreamer") {
            $database->where("is_restreamer", 1);
        }
    }

    if (isAdmin($owner)) {
        if ($reseller_id !== null && $reseller_id !== "") {
            $database->where("member_id", $reseller_id);
        }

        if (!empty($search_value)) {
            $database->where(
                "((`id` LIKE ?) OR (`username` LIKE ?) OR (`phone` LIKE ?) OR (`email` LIKE ?) OR (`reseller_notes` LIKE ?))",
                array('%' . $search_value . '%', '%' . $search_value . '%', '%' . $search_value . '%', '%' . $search_value . '%', '%' . $search_value . '%')
            );
        }
    } else {
        $resellers = array($owner_id);
        $allowed_resellers = array_merge($resellers, getAllResellersIdByOwnerID($owner_id));

        if ($reseller_id !== null && $reseller_id !== "") {
            $database->where("member_id", $reseller_id);
        } else {
            $database->where("`member_id`", $allowed_resellers, "IN");
        }

        if (!empty($search_value)) {
            $database->where(
                "((`id` LIKE ?) OR (`username` LIKE ?) OR (`phone` LIKE ?) OR (`email` LIKE ?) OR (`reseller_notes` LIKE ?))",
                array('%' . $search_value . '%', '%' . $search_value . '%', '%' . $search_value . '%', '%' . $search_value . '%', '%' . $search_value . '%')
            );
        }
    }

    $canAddScreen = true;
    $password = getServerProperty("code_default_pass");
    if ($code) {
        $database->where("`password` = '" . $password . "'");
        if (getServerProperty('code_max_connections_status', 0)) {
            if (getServerProperty('code_max_connections', 1) < 2) {
                $canAddScreen = false;
            }
        }
        $columns_name = ["`id`", "`username`", "`email`", "`created_at`", "`exp_date`", "`member_id`", "`max_connections`", "`reseller_notes`"];
    } else {
        $database->where("`password` != '" . $password . "'");
        if (getServerProperty('iptv_max_connections_status', 0)) {
            if (getServerProperty('iptv_max_connections', 1) < 2) {
                $canAddScreen = false;
            }
        }
        $columns_name = ["`id`", "`username`", "`password`", "`email`", "`created_at`", "`exp_date`", "`member_id`", "`max_connections`", "`reseller_notes`"];
    }

    // Adiciona a cláusula ORDER BY para ordenar os resultados de acordo com a coluna selecionada e o tipo de ordenação
    if ($order_column_index !== NULL &&  $order_type !== NULL && in_array($order_column_index, range(0, count($columns_name) - 1))) {

        $order_column = $columns_name[$order_column_index];
        $order_type = $order_type == "asc" ? "ASC" : "DESC";
        $database->orderBy($order_column, $order_type);
    }
    if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
        $all_clients = $database->get("`lines`", [$start, $length]);
    } else {
        $all_clients = $database->get("`users`", [$start, $length]);
    }
    // print_r($database->getLastQuery());
    // die();

    if (!empty($all_clients)) {
        // busca os IDs dos revendedores
        $reseller_ids = array_unique(array_column($all_clients, "member_id"));

        // busca os nomes dos revendedores associados aos IDs
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $owners = $database->where("id", $reseller_ids, "IN")->get("users", null, ["id", "username"]);
        } else {
            $owners = $database->where("id", $reseller_ids, "IN")->get("reg_users", null, ["id", "username"]);
        }

        // cria um array associativo dos nomes dos revendedores usando o ID como chave
        $owner_names = array_column($owners, "username", "id");

        $results = array();
        foreach ($all_clients as $current_user) {
            $current_user["reseller_name"] = isset($owner_names[$current_user["member_id"]]) ? $owner_names[$current_user["member_id"]] : "-";
            array_push($results, $current_user);
        }
        if (!isAdmin($owner)) {
            //remove os cliente que o member_id não estão na array $allowed_resellers
            $results = array_filter($results, function ($item) use ($allowed_resellers) {
                return in_array($item['member_id'], $allowed_resellers);
            });
        }
    } else {
        $results = array();
    }

    $canAddScreen = true;
    if (getServerProperty('iptv_max_connections_status', 0)) {
        if (getServerProperty('iptv_max_connections', 1) < 2) {
            $canAddScreen = false;
        }
    }

    foreach ($results as $current_reseller) {
        $status = "";
        if ($current_reseller["admin_enabled"] && $current_reseller["enabled"]) {
            $status = "<span class=\"badge badge-success\">Ativo</span>";
            if (!$current_reseller["exp_date"] || time() < $current_reseller["exp_date"]) {
                $status = "<span class=\"badge badge-success\">Ativo</span>";
            } else {
                $status = "<span class=\"badge badge-warning\">Expirado</span>";
            }
        } else {
            $status = "<span class=\"badge badge-danger\">Desativado</span>";
        }

        $action = '<div class="actions text-center" style="
        display: flex;
        justify-content: center;
    ">';
        if ($code) {
            $action .= '<a href="/codes/edit/' . $current_reseller['id'] . '" class="btn btn-icon text-muted" data-toggle="tooltip" data-original-title="Editar Código" data-id="' . $current_reseller['id'] . '"><i class="fad fa-user-edit" aria-hidden="true" style="font-size: 16px --fa-secondary-opacity: 1.0; --fa-secondary-color: dodgerblue;"></i></a>';
        } else {
            $action .= '<a href="/iptv/edit/' . $current_reseller['id'] . '" class="btn btn-icon text-muted" data-toggle="tooltip" data-original-title="Editar Cliente" data-id="' . $current_reseller['id'] . '"><i class="fad fa-user-edit" aria-hidden="true" style="font-size: 16px --fa-secondary-opacity: 1.0; --fa-secondary-color: dodgerblue;"></i></a>';
        }
        $action .= '<a href="#" class="btn btn-icon text-blue btfastmessage" data-toggle="tooltip" data-original-title="Mensagem Rápida" data-id="' . $current_reseller['id'] . '"><i class="far fa-sticky-note" aria-hidden="true" style="font-size: 16px"></i></a>';
        if (!$code && getServerProperty('iptv_show_m3u_link', 1)) {
            $action .= '<a href="#" class="btn btn-icon text-gray btlink" data-toggle="tooltip" data-original-title="Gerar Link" data-id="' . $current_reseller['id'] . '" data-user="' . $current_reseller['username'] . '" data-pass="' . $current_reseller['password'] . '"><i class="far fa-link" aria-hidden="true" style="font-size: 16px"></i></a>';
        }
        $action .= '<a href="#" class="btn btn-icon text-green btrenewplus" data-toggle="tooltip" data-original-title="Renovar vários meses - custo depende da quantidade de meses e telas." data-id="' . $current_reseller['id'] . '" data-text="Usuario: ' . $current_reseller['username'] . '"><i class="fad fa-calendar-alt" aria-hidden="true" style="font-size: 16px"></i></a>';
        if ($canAddScreen) {
            $action .= '<a href="#" class="btn btn-icon bttela" style="color: #7bc1ff" data-toggle="tooltip" data-original-title="Aumentar 1 tela - custo 1 credito(s)." data-id="' . $current_reseller['id'] . '" data-text="Usuario: ' . $current_reseller['username'] . ' <br> Creditos a ser consumido: 1"><i class="fad fa-desktop-alt" aria-hidden="true" style="font-size: 16px"></i></a>';
        }
        if (!$code && binStreamEnabled()['success'] && hasPermissionResource($owner_id, "binstream")) {
            $action .= '<a href="#" class="btn btn-icon btconvert" style="color: #00c4ff" data-toggle="tooltip" data-original-title="Converter para P2P" data-id="' . $current_reseller['id'] . '" data-text="Usuario: ' . $current_reseller['username'] . '"><i class="fad fa-exchange" aria-hidden="true" style="font-size: 16px"></i></a>';
        }
        $action .= '<a href="#" class="btn btn-icon text-yellow btblock" data-toggle="tooltip" data-original-title="Bloquear/Desbloquear" data-id="' . $current_reseller['id'] . '" data-text="Bloquear/desbloquear o usuário: ' . $current_reseller['username'] . '"><i class="far fa-ban" aria-hidden="true" style="font-size: 16px"></i></a>';
        $action .= '<a href="#" class="btn btn-icon text-red btdelete" data-toggle="tooltip" data-original-title="Deletar Cliente" data-id="' . $current_reseller['id'] . '" data-text="Deletar o cliente: ' . $current_reseller['username'] . '"><i class="far fa-user-slash" aria-hidden="true" style="font-size: 16px"></i></a>';
        $action .= '</div>';

        $result["data"][] = array(
            "id" => $current_reseller["id"],
            "display_username" => $current_reseller["is_trial"] ? "<i class=\"fad fa-clock  text-warning \" data-toggle=\"tooltip\" data-original-title=\"Sou um Teste\"></i> " . $current_reseller["username"] : $current_reseller["username"],
            "password" => $current_reseller["password"],
            "email" => $current_reseller["email"],
            "exp_date" => !empty($current_reseller['exp_date']) ? date("d/m/Y H:i", $current_reseller['exp_date']) : "",
            "created_at" => !empty($current_reseller['created_at']) ? date("d/m/Y H:i", $current_reseller['created_at']) : "",
            "max_connections" => $current_reseller["max_connections"],
            "reseller_name" => $current_reseller["reseller_name"],
            "reseller_notes" => "<span data-toggle=\"tooltip\" data-original-title=\"" . $current_reseller["reseller_notes"] . "\">" . str_limit($current_reseller["reseller_notes"], 25) . "</span>",
            "status" => $status,
            "action" => $action,
        );
    }
    return $result;
}


function getAllClientsByOwner($user, $p2p = false, $expiring = false)
{
    $resellers = array($user["id"]);
    $resellers = array_merge($resellers, getAllResellersIdByOwnerID($user["id"]));
    $password = getServerProperty("code_default_pass");
    if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
        if ($p2p) {
            $sql_select = "SELECT t1.*, t2.username as 'reseller_name' FROM `lines` t1, `users` t2 WHERE t1.password = :password AND t1.member_id = t2.id AND t1.member_id IN (" . implode(",", $resellers) . ")";
        } elseif ($expiring) {
            $now = strtotime("now");
            $yesterday = $now - 172800;
            $nextweek = $now + 604800;
            //$sql_select = "SELECT * FROM `users` WHERE `exp_date` > " . $yesterday . " and `exp_date` < " . $nextweek . " ORDER BY `id` DESC;";
            $sql_select = "SELECT t1.*, t2.username as 'reseller_name' FROM `lines` t1, `users` t2 WHERE t1.exp_date > " . $yesterday . " and t1.exp_date < " . $nextweek . " AND t1.member_id = t2.id AND t1.member_id IN (" . implode(",", $resellers) . ")";
        } else {
            $sql_select = "SELECT t1.*, t2.username as 'reseller_name' FROM `lines` t1, `users` t2 WHERE t1.password != :password AND t1.member_id = t2.id AND t1.member_id IN (" . implode(",", $resellers) . ")";
        }
    } else {
        if ($p2p) {
            $sql_select = "SELECT t1.*, t2.username as 'reseller_name' FROM `users` t1, `reg_users` t2 WHERE t1.password = :password AND t1.member_id = t2.id AND t1.member_id IN (" . implode(",", $resellers) . ")";
        } elseif ($expiring) {
            $now = strtotime("now");
            $yesterday = $now - 172800;
            $nextweek = $now + 604800;
            //$sql_select = "SELECT * FROM `users` WHERE `exp_date` > " . $yesterday . " and `exp_date` < " . $nextweek . " ORDER BY `id` DESC;";
            $sql_select = "SELECT t1.*, t2.username as 'reseller_name' FROM `users` t1, `reg_users` t2 WHERE t1.exp_date > " . $yesterday . " and t1.exp_date < " . $nextweek . " AND t1.member_id = t2.id AND t1.member_id IN (" . implode(",", $resellers) . ")";
        } else {
            $sql_select = "SELECT t1.*, t2.username as 'reseller_name' FROM `users` t1, `reg_users` t2 WHERE t1.password != :password AND t1.member_id = t2.id AND t1.member_id IN (" . implode(",", $resellers) . ")";
        }
    }
    $PDO = getconnection();
    if ($PDO !== NULL) {
        #$total_sql = $sql_select;
        $database = $PDO->prepare($sql_select);
        $database->bindParam(":password", $password, PDO::PARAM_STR, 255);
        $database->execute();
        $result = $database->fetchAll(PDO::FETCH_ASSOC);
        if ($result) {
            return $result;
        }
    }
    return array();
}

// function getAllClientsByOwnerWithOptions($user, $start = 0, $length = 10, $columns = array(), $search_value = "", $order_column_index = NULL, $order_type = "asc", $p2p = false, $expiring = false)
// {
//     $result = array("data" => array(), "recordsTotal" => 0, "recordsFiltered" => 0);
//     $all_clients = dataOutput($columns, getallclientsbyowner($user, $p2p, $expiring));
//     if ($order_column_index !== NULL && isset($columns[$order_column_index]["db"])) {
//         $order_column = $columns[$order_column_index]["db"];
//         usort($all_clients, function ($a, $b) use ($order_column, $order_type) {
//             if ($a[$order_column] === $b[$order_column]) {
//                 return 0;
//             }
//             if ($order_type == "asc") {
//                 return strip_tags($b[$order_column]) < strip_tags($a[$order_column]) ? 1 : -1;
//             }
//             return strip_tags($a[$order_column]) < strip_tags($b[$order_column]) ? 1 : -1;
//         });
//     }
//     $current_index = 0;
//     foreach ($all_clients as $current_client) {
//         if (tryFind($current_client, $columns, $search_value)) {
//             if ($start <= $current_index && count($result["data"]) < $length) {
//                 $result["data"][] = $current_client;
//             }
//             $current_index++;
//         }
//     }
//     $result["recordsTotal"] = count($all_clients);
//     $result["recordsFiltered"] = $current_index;
//     return $result;
// }

function getAllClientsExpiringWithOptions($user, $start = 0, $length = 10, $columns = array(), $search_value = "", $order_column_index = 2, $order_type = "asc", $tree = false)
{
    $result = array("data" => array(), "recordsTotal" => 0, "recordsFiltered" => 0);
    $all_clients = dataOutput($columns, getAllClientsExpiring($user, $tree));
    // if ($order_column_index !== NULL && isset($columns[$order_column_index]["db"])) {
    //     $order_column = $columns[$order_column_index]["db"];
    //     usort($all_clients, function ($a, $b) use ($order_column, $order_type) {
    //         if ($a[$order_column] === $b[$order_column]) {
    //             return 0;
    //         }
    //         if ($order_type == "asc") {
    //             return strip_tags($b[$order_column]) < strip_tags($a[$order_column]) ? 1 : -1;
    //         }
    //         return strip_tags($a[$order_column]) < strip_tags($b[$order_column]) ? 1 : -1;
    //     });
    // }
    $current_index = 0;
    foreach ($all_clients as $current_client) {
        if (tryFind($current_client, $columns, $search_value)) {
            if ($start <= $current_index && count($result["data"]) < $length) {
                $result["data"][] = $current_client;
            }
            $current_index++;
        }
    }
    $result["recordsTotal"] = count($all_clients);
    $result["recordsFiltered"] = $current_index;
    return $result;
}

function getAllClientsP2PExpiringWithOptions($user, $start = 0, $length = 10, $columns = array(), $search_value = "", $order_column_index = 2, $order_type = "asc", $tree = false)
{
    $result = array("data" => array(), "recordsTotal" => 0, "recordsFiltered" => 0);
    $all_clients = dataOutput($columns, getAllClientsP2PExpiring($user, $tree));
    $current_index = 0;
    foreach ($all_clients as $current_client) {
        if (tryFind($current_client, $columns, $search_value)) {
            if ($start <= $current_index && count($result["data"]) < $length) {
                $result["data"][] = $current_client;
            }
            $current_index++;
        }
    }
    $result["recordsTotal"] = count($all_clients);
    $result["recordsFiltered"] = $current_index;
    return $result;
}

function getAllClientsExpiring($user, $tree = false)
{
    global $redis;

    $key = OFFICE_CONFIG['panel_id'] . "_userid_" . $user["id"] . "_clients_expiring";
    $cached_result = $redis->get($key);
    if ($cached_result) {
        return json_decode($cached_result, true);
    }

    $resellers = array($user["id"]);
    if ($tree) {
        $resellers = array_merge($resellers, getAllResellersIdByOwnerID($user["id"]));
    }

    $now = time();
    $yesterday = $now - 172800;
    $nextweek = $now + 604800;
    if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
        $sql_select = "SELECT t1.id, t1.member_id, t1.username, t1.password, t1.phone, t1.exp_date, t1.is_trial, t2.username as 'reseller_name' FROM `lines` t1, `users` t2 WHERE t1.is_trial = 0 AND t1.exp_date > " . $yesterday . " and t1.exp_date < " . $nextweek . " AND t1.member_id = t2.id AND t1.member_id IN (" . implode(",", $resellers) . ")  ORDER BY t1.exp_date ASC";
    } else {
        $sql_select = "SELECT t1.id, t1.member_id, t1.username, t1.password, t1.phone, t1.exp_date, t1.is_trial, t2.username as 'reseller_name' FROM `users` t1, `reg_users` t2 WHERE t1.is_trial = 0 AND t1.exp_date > " . $yesterday . " and t1.exp_date < " . $nextweek . " AND t1.member_id = t2.id AND t1.member_id IN (" . implode(",", $resellers) . ")  ORDER BY t1.exp_date ASC";
    }

    $PDO = getconnection();
    if ($PDO !== NULL) {
        #$total_sql = $sql_select;
        $database = $PDO->prepare($sql_select);
        $database->execute();
        $result = $database->fetchAll(PDO::FETCH_ASSOC);
        if ($result) {
            $redis->setex($key, 3600, json_encode($result)); //3600 = 1 hour
            return $result;
        }
    }
    return array();
}

function getAllClientsP2PExpiring($user, $tree = false)
{
    global $redis;

    $key = OFFICE_CONFIG['panel_id'] . "_userid_" . $user["id"] . "_clients_expiring_p2p";
    $cached_result = $redis->get($key);
    if ($cached_result) {
        return json_decode($cached_result, true);
    }

    $resellers = array($user["id"]);
    if ($tree) {
        $resellers = array_merge($resellers, getAllResellersIdByOwnerID($user["id"]));
    }

    $now = time();
    $yesterday = $now - 172800;
    $nextweek = $now + 604800;
    //timestamp to GMT String
    $yesterday = gmdate("Y-m-d H:i:s", $yesterday);
    $nextweek = gmdate("Y-m-d H:i:s", $nextweek);
    include_once __DIR__ . "/class/binstream.php";

    $binstream = new BinStream();
    $filter = array(
        "endTime__gte" => $yesterday,
        "endTime__lte" => $nextweek,
        "type" => 1
    );
    $result = $binstream->getusers($resellers, "-_endTime", $filter);

    if ($result) {
        $redis->setex($key, 3600, json_encode($result)); //3600 = 1 hour
        return $result;
    }

    return array();
}

function tryFind($array = array(), $columns = array(), $search_value)
{
    if (empty($search_value)) {
        return true;
    }
    foreach ($columns as $current_column) {
        $searchable = isset($current_column["searchable"]) ? $current_column["searchable"] : true;
        if ($searchable) {
            $striped_db_value = @strip_tags($array[$current_column["db"]]);
            if (stripos($striped_db_value, $search_value) !== false) {
                return true;
            }
        }
    }
    return false;
}
function dataOutput($columns, $data)
{
    $out = array();
    $i = 0;
    for ($ien = count($data); $i < $ien; $i++) {
        $row = array();
        $j = 0;
        for ($jen = count($columns); $j < $jen; $j++) {
            $column = $columns[$j];
            $db_value = isset($data[$i][$column["db"]]) ? $data[$i][$column["db"]] : "";
            if (isset($column["formatter"])) {
                $row[$column["db"]] = $column["formatter"]($db_value, $data[$i]);
            } else {
                $row[$column["db"]] = $db_value;
            }
        }
        $out[] = $row;
    }
    return $out;
}
function getClientsByOwnerID($userid)
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "SELECT * FROM `lines` WHERE `member_id` = :userid;";
        } else {
            $sql = "SELECT * FROM `users` WHERE `member_id` = :userid;";
        }
        $database = $PDO->prepare($sql);
        $database->bindParam(":userid", $userid, PDO::PARAM_INT);
        $database->execute();
        return $database->fetchAll(PDO::FETCH_ASSOC);
    }
    return array();
}
function getAllOnlineClients($p2p = false)
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        $password = getServerProperty("code_default_pass");
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            if ($p2p) {
                $sql = "select lines.id, lines.username, streams.stream_display_name as `stream_name`, lines_live.user_ip, lines_live.date_start as `time`, lines_live.geoip_country_code as `country`, lines_live.isp as `internet_server`, lines_live.divergence as `divergence` from `lines`, `lines_live`, `streams` where lines.id=lines_live.user_id and lines_live.stream_id = streams.id and lines.password = :password";
            } else {
                $sql = "select lines.id, lines.username, streams.stream_display_name as `stream_name`, lines_live.user_ip, lines_live.date_start as `time`, lines_live.geoip_country_code as `country`, lines_live.isp as `internet_server`, lines_live.divergence as `divergence` from `lines`, `lines_live`, `streams` where lines.id=lines_live.user_id and lines_live.stream_id = streams.id and lines.password != :password";
            }
        } else {
            if ($p2p) {
                $sql = "select users.id, users.username, streams.stream_display_name as `stream_name`, user_activity_now.user_ip, user_activity_now.date_start as `time`, user_activity_now.geoip_country_code as `country`, user_activity_now.isp as `internet_server`, user_activity_now.divergence as `divergence` from users, user_activity_now, streams where users.id=user_activity_now.user_id and user_activity_now.stream_id = streams.id and users.password = :password";
            } else {
                $sql = "select users.id, users.username, streams.stream_display_name as `stream_name`, user_activity_now.user_ip, user_activity_now.date_start as `time`, user_activity_now.geoip_country_code as `country`, user_activity_now.isp as `internet_server`, user_activity_now.divergence as `divergence` from users, user_activity_now, streams where users.id=user_activity_now.user_id and user_activity_now.stream_id = streams.id and users.password != :password";
            }
        }
        $database = $PDO->prepare($sql);
        $database->bindParam(":password", $password, PDO::PARAM_STR, 255);
        $database->execute();
        return $database->fetchAll(PDO::FETCH_ASSOC);
    }
    return array();
}
function getAllOnlineClientsWithOptions($start = 0, $length = 10, $columns = array(), $search_value = "", $order_column_index = NULL, $order_type = "asc", $p2p = false)
{
    $result = array("data" => array(), "recordsTotal" => 0, "recordsFiltered" => 0);
    $all_clients = dataoutput($columns, getallonlineclients($p2p));
    if ($order_column_index !== NULL && isset($columns[$order_column_index]["db"])) {
        $order_column = $columns[$order_column_index]["db"];
        usort($all_clients, function ($a, $b) use ($order_column, $order_type) {
            if ($a[$order_column] === $b[$order_column]) {
                return 0;
            }
            if ($order_type == "asc") {
                return strip_tags($b[$order_column]) < strip_tags($a[$order_column]) ? 1 : -1;
            }
            return strip_tags($a[$order_column]) < strip_tags($b[$order_column]) ? 1 : -1;
        });
    }
    $current_index = 0;
    foreach ($all_clients as $current_client) {
        if (tryfind($current_client, $columns, $search_value)) {
            if ($start <= $current_index && count($result["data"]) < $length) {
                $result["data"][] = $current_client;
            }
            $current_index++;
        }
    }
    $result["recordsTotal"] = count($all_clients);
    $result["recordsFiltered"] = $current_index;
    return $result;
}
function getAllOnlineClientsByOwnerID($userid, $p2p = false)
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        $password = getServerProperty("code_default_pass");
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            if ($p2p) {
                $sql = "select lines.id, lines.username, streams.stream_display_name as `stream_name`, lines_live.user_ip, lines_live.date_start as `time`,lines_live.geoip_country_code as `country`, lines_live.isp as `internet_server` from `lines`, `lines_live`, `streams` where lines.id=lines_live.user_id and lines_live.stream_id = streams.id and lines.member_id = :userid and lines.password = :password";
            } else {
                $sql = "select lines.id, lines.username, streams.stream_display_name as `stream_name`, lines_live.user_ip, lines_live.date_start as `time`,lines_live.geoip_country_code as `country`, lines_live.isp as `internet_server` from `lines`, `lines_live`, `streams` where lines.id=lines_live.user_id and lines_live.stream_id = streams.id and lines.member_id = :userid and lines.password != :password";
            }
        } else {
            if ($p2p) {
                $sql = "select users.id, users.username, streams.stream_display_name as `stream_name`, user_activity_now.user_ip, user_activity_now.date_start as `time`,user_activity_now.geoip_country_code as `country`, user_activity_now.isp as `internet_server` from users, user_activity_now, streams where users.id=user_activity_now.user_id and user_activity_now.stream_id = streams.id and users.member_id = :userid and users.password = :password";
            } else {
                $sql = "select users.id, users.username, streams.stream_display_name as `stream_name`, user_activity_now.user_ip, user_activity_now.date_start as `time`,user_activity_now.geoip_country_code as `country`, user_activity_now.isp as `internet_server` from users, user_activity_now, streams where users.id=user_activity_now.user_id and user_activity_now.stream_id = streams.id and users.member_id = :userid and users.password != :password";
            }
        }
        $database = $PDO->prepare($sql);
        $database->bindParam(":userid", $userid, PDO::PARAM_INT);
        $database->bindParam(":password", $password, PDO::PARAM_STR, 255);
        $database->execute();
        return $database->fetchAll(PDO::FETCH_ASSOC);
    }
    return array();
}
function getAllOnlineClientsByOwnerWithOptions($reseller, $start = 0, $length = 10, $columns = array(), $search_value = "", $order_column_index = NULL, $order_type = "asc", $p2p = false)
{
    $result = array("data" => array(), "recordsTotal" => 0, "recordsFiltered" => 0);
    $all_clients = dataoutput($columns, getallonlineclientsbyownerid($reseller["id"], $p2p));
    if ($order_column_index !== NULL && isset($columns[$order_column_index]["db"])) {
        $order_column = $columns[$order_column_index]["db"];
        usort($all_clients, function ($a, $b) use ($order_column, $order_type) {
            if ($a[$order_column] === $b[$order_column]) {
                return 0;
            }
            if ($order_type == "asc") {
                return strip_tags($b[$order_column]) < strip_tags($a[$order_column]) ? 1 : -1;
            }
            return strip_tags($a[$order_column]) < strip_tags($b[$order_column]) ? 1 : -1;
        });
    }
    $current_index = 0;
    foreach ($all_clients as $current_client) {
        if (tryfind($current_client, $columns, $search_value)) {
            if ($start <= $current_index && count($result["data"]) < $length) {
                $result["data"][] = $current_client;
            }
            $current_index++;
        }
    }
    $result["recordsTotal"] = count($all_clients);
    $result["recordsFiltered"] = $current_index;
    return $result;
}
function createReseller($owner_id, $username, $password, $credits, $member_group_id, $email, $notes)
{
    if (existReseller($username)) {
        return false;
    }
    if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
        $crypted_password = cryptPassword($password, "xui");
    } else {
        $crypted_password = cryptPassword($password, "xtreamcodes");
    }
    if ($member_group_id) {
        $settings = getServerSettings();
        if ($settings) {
            $language = $settings["default_lang"];
            return insertReseller($owner_id, $username, $crypted_password, $credits, $email, $notes, $member_group_id, $language);
        }
    }
    return false;
}
function insertReseller($owner_id, $username, $password, $credits, $email, $notes, $member_group_id, $language)
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "INSERT INTO `users` (`id`, `username`, `password`, `email`, `ip`, `date_registered`, `last_login`, `member_group_id`, `credits`, `notes`, `status`, `reseller_dns`, `owner_id`, `override_packages`) VALUES (NULL, :username, :password, :email, NULL, unix_timestamp(NOW()), NULL, :member_group_id, :credits, :notes, 1, NULL, :owner_id, '[]')";
        } else {
            $sql = "INSERT INTO `reg_users` (`id`, `username`, `password`, `email`, `ip`, `date_registered`, `verify_key`, `last_login`, `member_group_id`, `verified`, `credits`, `notes`, `status`, `default_lang`, `reseller_dns`, `owner_id`, `override_packages`, `google_2fa_sec`) VALUES (NULL, :username, :password, :email, NULL, unix_timestamp(NOW()), NULL, NULL, :member_group_id, '1', :credits, :notes, '1', :language, '', :owner_id, NULL, '')";
        }

        $database = $PDO->prepare($sql);
        $database->bindParam(":owner_id", $owner_id, PDO::PARAM_INT);
        $database->bindParam(":username", $username, PDO::PARAM_STR, 255);
        $database->bindParam(":password", $password, PDO::PARAM_STR, 255);
        $database->bindParam(":credits", $credits, PDO::PARAM_INT);
        $database->bindParam(":email", $email, PDO::PARAM_STR, 255);
        $database->bindParam(":notes", $notes, PDO::PARAM_STR, 500);
        $database->bindParam(":member_group_id", $member_group_id, PDO::PARAM_INT);

        if (OFFICE_CONFIG['remote_db']['panel_type'] != "XUI") {
            $database->bindParam(":language", $language, PDO::PARAM_STR, 255);
        }
        if ($database->execute()) {
            return true;
        }
    }
    return false;
}
function existReseller($username)
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "SELECT `id` FROM `users` WHERE `username` = :username;";
        } else {
            $sql = "SELECT `id` FROM `reg_users` WHERE `username` = :username;";
        }
        $database = $PDO->prepare($sql);
        $database->bindParam(":username", $username, PDO::PARAM_STR, 255);
        $database->execute();
        if (0 < $database->rowCount()) {
            return true;
        }
    }
    return false;
}
function getResellersAdmin($limit = null, $offset = null)
{
    $results = array();
    $all_users = getallusers($limit, $offset);
    foreach ($all_users as $current_user) {
        $reseller_key = array_search($current_user["owner_id"], array_column($all_users, "id"));
        $reseller_name = $reseller_key !== false ? $all_users[$reseller_key]["username"] : "-";
        $current_user["reseller_name"] = $reseller_name;
        array_push($results, $current_user);
    }
    return $results;
}
function getResellersByOwner($user)
{
    $results = array();
    $all_users = getallusers();
    $users = getAllResellersByOwnerID($user["id"]);
    foreach ($users as $current_user) {
        $reseller_key = array_search($current_user["owner_id"], array_column($all_users, "id"));
        $reseller_name = $reseller_key !== false ? $all_users[$reseller_key]["username"] : "-";
        $current_user["reseller_name"] = $reseller_name;
        array_push($results, $current_user);
    }
    return $results;
}
function toggleTicket($ticket_id)
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        $sql = "UPDATE `tickets` SET `status` = !`status` WHERE `id` = :ticket_id LIMIT 1;";
        $database = $PDO->prepare($sql);
        $database->bindParam(":ticket_id", $ticket_id, PDO::PARAM_INT);
        if ($database->execute()) {
            return true;
        }
    }
    return false;
}
function deleteTicket($ticket_id)
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        $sql = "DELETE FROM `tickets` WHERE `id` = :ticket_id LIMIT 1;";
        $database = $PDO->prepare($sql);
        $database->bindParam(":ticket_id", $ticket_id, PDO::PARAM_INT);
        if ($database->execute()) {
            $sql_two = "DELETE FROM `tickets_replies` WHERE `ticket_id` = :ticket_id;";
            $database = $PDO->prepare($sql_two);
            $database->bindParam(":ticket_id", $ticket_id, PDO::PARAM_INT);
            if ($database->execute()) {
                return true;
            }
        }
    }
    return false;
}
function updateReadTicket($ticket_id, $person, $read = 1)
{
    if ($person !== "admin" && $person !== "user") {
        return false;
    }
    $PDO = getconnection();
    if ($PDO !== NULL) {
        $sql = "UPDATE `tickets` SET `" . $person . "_read` = :read WHERE `id` = :ticket_id LIMIT 1;";
        $database = $PDO->prepare($sql);
        $database->bindParam(":ticket_id", $ticket_id, PDO::PARAM_INT);
        $database->bindParam(":read", $read, PDO::PARAM_INT);
        if ($database->execute()) {
            return true;
        }
    }
    return false;
}
function getTicketById($ticket_id)
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        $sql = "SELECT * FROM `tickets` WHERE `tickets`.id = :ticket_id LIMIT 1;";
        $database = $PDO->prepare($sql);
        $database->bindParam(":ticket_id", $ticket_id, PDO::PARAM_INT);
        if ($database->execute()) {
            return $database->fetch(PDO::FETCH_ASSOC);
        }
    }
    return false;
}
function getAllTickets()
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "SELECT `tickets`.id, `users`.username as 'reseller', `tickets`.title, `tickets`.status, `tickets`.admin_read, `tickets`.user_read FROM `tickets`, `users` WHERE `tickets`.member_id = `users`.id";
        } else {
            $sql = "SELECT `tickets`.id, `reg_users`.username as 'reseller', `tickets`.title, `tickets`.status, `tickets`.admin_read, `tickets`.user_read FROM `tickets`, `reg_users` WHERE `tickets`.member_id = `reg_users`.id";
        }
        $database = $PDO->prepare($sql);
        if ($database->execute()) {
            return $database->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    return array();
}
function getAllTicketsByReseller($reseller_id)
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "SELECT `tickets`.id, `users`.username as 'reseller', `tickets`.title, `tickets`.status, `tickets`.admin_read, `tickets`.user_read FROM `tickets`, `users` WHERE `tickets`.member_id = :member_id AND `tickets`.member_id = `users`.id";
        } else {
            $sql = "SELECT `tickets`.id, `reg_users`.username as 'reseller', `tickets`.title, `tickets`.status, `tickets`.admin_read, `tickets`.user_read FROM `tickets`, `reg_users` WHERE `tickets`.member_id = :member_id AND `tickets`.member_id = `reg_users`.id";
        }
        $database = $PDO->prepare($sql);
        $database->bindParam(":member_id", $reseller_id, PDO::PARAM_INT);
        if ($database->execute()) {
            return $database->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    return array();
}
function getUnreadTicketsCount($reseller)
{
    if (isAdmin($reseller)) {
        $where = "`tickets`.admin_read = 0 AND status = 1";
    } else {
        $where = "`tickets`.member_id = :member_id AND user_read = 0";
    }
    $PDO = getconnection();
    if ($PDO !== NULL) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "SELECT COUNT(*) as 'count' FROM `tickets` WHERE  " . $where;
        } else {
            $sql = "SELECT COUNT(*) as 'count' FROM `tickets` WHERE  " . $where;
        }
        // return $sql;
        $database = $PDO->prepare($sql);
        if (!isAdmin($reseller)) {
            $database->bindParam(":member_id", $reseller['id'], PDO::PARAM_INT);
        }
        if ($database->execute()) {
            $result = $database->fetch(PDO::FETCH_ASSOC);
            return $result["count"];
        }
    }
    return 0;
}

function getAllTicketsAdminWithOptions($start = 0, $length = 10, $columns = array(), $search_value = "", $order_column_index = NULL, $order_type = "asc")
{
    $result = array("data" => array(), "recordsTotal" => 0, "recordsFiltered" => 0);
    $all_resellers = dataoutput($columns, getalltickets());
    if ($order_column_index !== NULL && isset($columns[$order_column_index]["db"])) {
        $order_column = $columns[$order_column_index]["db"];
        usort($all_resellers, function ($a, $b) use ($order_column, $order_type) {
            if ($a[$order_column] === $b[$order_column]) {
                return 0;
            }
            if ($order_type == "asc") {
                return strip_tags($b[$order_column]) < strip_tags($a[$order_column]) ? 1 : -1;
            }
            return strip_tags($a[$order_column]) < strip_tags($b[$order_column]) ? 1 : -1;
        });
    }
    $current_index = 0;
    foreach ($all_resellers as $current_reseller) {
        if (tryfind($current_reseller, $columns, $search_value)) {
            if ($start <= $current_index && count($result["data"]) < $length) {
                $result["data"][] = $current_reseller;
            }
            $current_index++;
        }
    }
    $result["recordsTotal"] = count($all_resellers);
    $result["recordsFiltered"] = $current_index;
    return $result;
}
function getAllTicketsByOwnerWithOptions($reseller, $start = 0, $length = 10, $columns = array(), $search_value = "", $order_column_index = NULL, $order_type = "asc")
{
    $result = array("data" => array(), "recordsTotal" => 0, "recordsFiltered" => 0);
    $all_resellers = dataoutput($columns, getallticketsbyreseller($reseller["id"]));
    if ($order_column_index !== NULL && isset($columns[$order_column_index]["db"])) {
        $order_column = $columns[$order_column_index]["db"];
        usort($all_resellers, function ($a, $b) use ($order_column, $order_type) {
            if ($a[$order_column] === $b[$order_column]) {
                return 0;
            }
            if ($order_type == "asc") {
                return strip_tags($b[$order_column]) < strip_tags($a[$order_column]) ? 1 : -1;
            }
            return strip_tags($a[$order_column]) < strip_tags($b[$order_column]) ? 1 : -1;
        });
    }
    $current_index = 0;
    foreach ($all_resellers as $current_reseller) {
        if (tryfind($current_reseller, $columns, $search_value)) {
            if ($start <= $current_index && count($result["data"]) < $length) {
                $result["data"][] = $current_reseller;
            }
            $current_index++;
        }
    }
    $result["recordsTotal"] = count($all_resellers);
    $result["recordsFiltered"] = $current_index;
    return $result;
}

// function getAllResellersAdminWithOptions($start = 0, $length = 10, $columns = array(), $search_value = "", $order_column_index = NULL, $order_type = "asc")
// {
//     $result = array("data" => array(), "recordsTotal" => 0, "recordsFiltered" => 0);
//     $all_resellers = dataoutput($columns, getresellersadmin($length, $start));
//     if ($order_column_index !== NULL && isset($columns[$order_column_index]["db"])) {
//         $order_column = $columns[$order_column_index]["db"];
//         usort($all_resellers, function ($a, $b) use ($order_column, $order_type) {
//             if ($a[$order_column] === $b[$order_column]) {
//                 return 0;
//             }
//             if ($order_type == "asc") {
//                 return strip_tags($b[$order_column]) < strip_tags($a[$order_column]) ? 1 : -1;
//             }
//             return strip_tags($a[$order_column]) < strip_tags($b[$order_column]) ? 1 : -1;
//         });
//     }
//     $current_index = $start;
//     foreach ($all_resellers as $current_reseller) {
//         if (tryfind($current_reseller, $columns, $search_value)) {
//             if (count($result["data"]) < $length) {
//                 $result["data"][] = $current_reseller;
//             }
//             $current_index++;
//         }
//     }
//     $result["recordsTotal"] = getAllUsersCount();
//     $result["recordsFiltered"] = getAllUsersCount();
//     return $result;
// }

function getAllResellersAdminWithOptions($start = 0, $length = 10, $columns = array(), $search_value = "", $order_column_index = NULL, $order_type = "asc")
{
    global $database;
    $result = array("data" => array(), "recordsTotal" => 0, "recordsFiltered" => 0);

    // Query para contar o número total de registros
    $result["recordsTotal"] = getAllUsersCount();
    $result["recordsFiltered"] = $result["recordsTotal"];

    // if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
    //     $query = "SELECT * FROM `users`";
    // } else {
    //     $query = "SELECT * FROM `reg_users`";
    // }

    // Query para buscar os dados

    // Adiciona a cláusula WHERE para filtrar os resultados de acordo com o valor de busca
    // if (!empty($search_value)) {
    //     $query .= " WHERE username LIKE '%$search_value%' OR email LIKE '%$search_value%' OR ip LIKE '%$search_value%'";
    // }

    if (!empty($search_value)) {
        $database->where("id", '%' . $search_value . '%', 'like');
        $database->orWhere("username", '%' . $search_value . '%', 'like');
        $database->orWhere("email", '%' . $search_value . '%', 'like');
        $database->orWhere("ip", '%' . $search_value . '%', 'like');
        $database->orWhere("notes", '%' . $search_value . '%', 'like');
    }

    // Adiciona a cláusula ORDER BY para ordenar os resultados de acordo com a coluna selecionada e o tipo de ordenação
    if ($order_column_index !== NULL && isset($columns[$order_column_index]["db"])) {
        $order_column = $columns[$order_column_index]["db"];
        $order_type = $order_type == "asc" ? "ASC" : "DESC";
        $database->orderBy($order_column, $order_type);

        // $query .= " ORDER BY $order_column $order_type";
    }

    // Adiciona a cláusula LIMIT para limitar o número de registros

    if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
        $all_resellers = $database->get("`users`", [$start, $length]);
    } else {
        $all_resellers = $database->get("`reg_users`", [$start, $length]);
    }
    // $query .= " LIMIT $length OFFSET $start";

    // $all_resellers = $database->rawQuery($query);
    //verifica se $all_resellers é vazio
    if (!empty($all_resellers)) {


        // busca os IDs dos revendedores
        $reseller_ids = array_unique(array_column($all_resellers, "owner_id"));

        // busca os nomes dos revendedores associados aos IDs
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $owners = $database->where("id", $reseller_ids, "IN")->get("users", null, ["id", "username"]);
        } else {
            $owners = $database->where("id", $reseller_ids, "IN")->get("reg_users", null, ["id", "username"]);
        }

        // cria um array associativo dos nomes dos revendedores usando o ID como chave
        $owner_names = array_column($owners, "username", "id");

        $results = array();
        foreach ($all_resellers as $current_user) {
            $current_user["reseller_name"] = isset($owner_names[$current_user["owner_id"]]) ? $owner_names[$current_user["owner_id"]] : "-";
            array_push($results, $current_user);
        }
    } else {
        $results = array();
    }

    // $results = dataoutput($columns, $results);

    $isAdmin = isAdmin(getLoggedUser());
    foreach ($results as $current_reseller) {
        $action = '<div class="actions text-center">';
        $action .= '<a href="/reseller/edit/' . $current_reseller['id'] . '" class="btn btn-icon text-muted" data-toggle="tooltip" data-original-title="Editar Revendedor" data-id="' . $current_reseller['id'] . '"><i class="fad fa-user-edit" aria-hidden="true" style="font-size: 16px --fa-secondary-opacity: 1.0; --fa-secondary-color: dodgerblue;"></i></a>';
        $action .= '<a href="#" class="btn btn-icon text-green btcredits" data-toggle="tooltip" data-original-title="Adic/Remover Creditos" data-id="' . $current_reseller['id'] . '" data-text="Adicionar/remover creditos do revendedor: ' . $current_reseller['username'] . '"><i class="far fa-dollar-sign" aria-hidden="true" style="font-size: 16px"></i></a>';
        $action .= '<a href="#" class="btn btn-icon text-yellow btblock" data-toggle="tooltip" data-original-title="Bloquear/Desbloquear" data-id="' . $current_reseller['id'] . '" data-text="Bloquear/desbloquear o revendedor: ' . $current_reseller['username'] . '"><i class="far fa-ban" aria-hidden="true" style="font-size: 16px"></i></a>';
        $action .= '<a href="#" class="btn btn-icon text-red btdelete" data-toggle="tooltip" data-original-title="Deletar Revendedor" data-id="' . $current_reseller['id'] . '" data-text="Deletar o revendedor: ' . $current_reseller['username'] . '"><i class="far fa-user-slash" aria-hidden="true" style="font-size: 16px"></i></a>';
        if ($isAdmin) {
            $action .= '<a href="#" class="btn btn-icon text-blue btresellerlogin" data-toggle="tooltip" data-original-title="Logar como revendedor" data-id="' . $current_reseller['id'] . '" data-text="Logar como revendedor: ' . $current_reseller['username'] . '"><i class="far fa-external-link" aria-hidden="true" style="font-size: 16px"></i></a>';
        }
        $action .= '</div>';

        $result["data"][] = array(
            "id" => $current_reseller["id"],
            "username" => $current_reseller["username"],
            "email" => $current_reseller["email"],
            "last_recharge" => getUserProperty($current_reseller["id"], "last_recharge"),
            "ip" =>  "<span data-toggle=\"tooltip\" data-original-title=\"" . $current_reseller["ip"] . "\">" . str_limit($current_reseller["ip"], 15) . "</span>",
            "credits" => $current_reseller["credits"],
            "reseller_name" => $current_reseller["reseller_name"],
            "notes" => "<span data-toggle=\"tooltip\" data-original-title=\"" . $current_reseller["notes"] . "\">" . str_limit($current_reseller["notes"], 15) . "</span>",
            "status" => $current_reseller["status"] ? "<span class=\"badge badge-success\">Ativo</span>" : "<span class=\"badge badge-danger\">Bloqueado</span>",
            "action" => $action,
        );
    }

    return $result;
}

function getAllResellersByOwnerID($userid)
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "select * from (select * from users order by owner_id, id) users_sorted, (select @pv := :userid) initialisation where find_in_set(owner_id, @pv) and length(@pv := concat(@pv, ',', id));";
        } else {
            $sql = "select * from (select * from reg_users order by owner_id, id) users_sorted, (select @pv := :userid) initialisation where find_in_set(owner_id, @pv) and length(@pv := concat(@pv, ',', id));";
        }
        $database = $PDO->prepare($sql);
        $database->bindParam(":userid", $userid, PDO::PARAM_INT);
        $database->execute();
        return $database->fetchAll(PDO::FETCH_ASSOC);
    }
    return array();
}

function getAllResellersIdByOwnerID($userid)
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "select `id` from (select * from users order by owner_id, id) users_sorted, (select @pv := :userid) initialisation where find_in_set(owner_id, @pv) and length(@pv := concat(@pv, ',', id));";
        } else {
            $sql = "select `id` from (select * from reg_users order by owner_id, id) users_sorted, (select @pv := :userid) initialisation where find_in_set(owner_id, @pv) and length(@pv := concat(@pv, ',', id));";
        }
        $database = $PDO->prepare($sql);
        $database->bindParam(":userid", $userid, PDO::PARAM_INT);
        $database->execute();
        return $database->fetchAll(PDO::FETCH_COLUMN);
    }
    return array();
}

function getAllResellersByOwnerWithOptions($reseller, $start = 0, $length = 10, $columns = array(), $search_value = "", $order_column_index = NULL, $order_type = "asc")
{
    $result = array("data" => array(), "recordsTotal" => 0, "recordsFiltered" => 0);
    $all_users = getallusers();
    $users = getResellersByOwnerID($reseller["id"]);
    foreach ($users as &$current_user) {
        $reseller_key = array_search($current_user["owner_id"], array_column($all_users, "id"));
        $reseller_name = $reseller_key !== false ? $all_users[$reseller_key]["username"] : "-";
        $current_user["reseller_name"] = $reseller_name;
    }
    $all_resellers = dataoutput($columns, $users);
    if ($order_column_index !== NULL && isset($columns[$order_column_index]["db"])) {
        $order_column = $columns[$order_column_index]["db"];
        usort($all_resellers, function ($a, $b) use ($order_column, $order_type) {
            if ($a[$order_column] === $b[$order_column]) {
                return 0;
            }
            if ($order_type == "asc") {
                return strip_tags($b[$order_column]) < strip_tags($a[$order_column]) ? 1 : -1;
            }
            return strip_tags($a[$order_column]) < strip_tags($b[$order_column]) ? 1 : -1;
        });
    }
    $current_index = 0;
    foreach ($all_resellers as $current_reseller) {
        if (tryfind($current_reseller, $columns, $search_value)) {
            if ($start <= $current_index && count($result["data"]) < $length) {
                $result["data"][] = $current_reseller;
            }
            $current_index++;
        }
    }
    $result["recordsTotal"] = count($all_resellers);
    $result["recordsFiltered"] = $current_index;
    return $result;
}

function getResellersByOwnerID($userid)
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "SELECT * FROM `users` WHERE `owner_id` = :userid;";
        } else {
            $sql = "SELECT * FROM `reg_users` WHERE `owner_id` = :userid;";
        }
        $database = $PDO->prepare($sql);
        $database->bindParam(":userid", $userid, PDO::PARAM_INT);
        $database->execute();
        return $database->fetchAll(PDO::FETCH_ASSOC);
    }
    return array();
}
function deleteReseller($reseller_id)
{
    deleteAllUserProperty($reseller_id);
    $PDO = getconnection();
    if ($PDO !== NULL) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "DELETE FROM `users` WHERE `id` = :reseller_id LIMIT 1;";
        } else {
            $sql = "DELETE FROM `reg_users` WHERE `id` = :reseller_id LIMIT 1;";
        }
        $database = $PDO->prepare($sql);
        $database->bindParam(":reseller_id", $reseller_id, PDO::PARAM_INT);
        if ($database->execute()) {
            return true;
        }
    }
    return false;
}
function getClientsCount($owner, $status = NULL, $type = NULL, $reseller_id = NULL, $code = false)
{
    return  getAllClientsCount($owner, $status, $type, $reseller_id, $code);
}
function getAllClientsCount($owner, $status = NULL, $type = NULL, $reseller_id = NULL, $code = false)
{
    global $database;
    if ($status !== NULL && $status !== "") {
        if ($status == "enabled") {
            $database->where("(`admin_enabled` = 1 AND `enabled` = 1 AND (`exp_date` > ? OR `exp_date` IS NULL))", array(time()));
        } elseif ($status == "disabled") {
            $database->where("(admin_enabled = 0 OR enabled = 0)");
        } elseif ($status == "expired") {
            $database->where("((`exp_date` < ?) AND (`exp_date` IS NOT NULL) AND (`admin_enabled` = 1) AND (`enabled` = 1))", array(time()));
        }
    }

    if ($type !== NULL && $type !== "") {
        if ($type == "official") {
            $database->where("is_trial", 0);
        } elseif ($type == "trial") {
            $database->where("is_trial", 1);
        } elseif ($type == "restreamer") {
            $database->where("is_restreamer", 1);
        }
    }

    if (isAdmin($owner)) {
        if ($reseller_id !== null && $reseller_id !== "") {
            $database->where("member_id", $reseller_id);
        }
    } else {
        $resellers = array($owner['id']);
        $resellers = array_merge($resellers, getAllResellersIdByOwnerID($owner['id']));

        if ($reseller_id !== null && $reseller_id !== "") {
            $database->where("member_id", $reseller_id);
        } else {
            $database->where("`member_id`", $resellers, "IN");
        }
    }

    $password = getServerProperty("code_default_pass");
    if ($code) {
        $database->where("`password` = '" . $password . "'");
    } else {
        $database->where("`password` != '" . $password . "'");
    }

    if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
        return $database->getValue("`lines`", "count(*)");
    } else {
        return $database->getValue("`users`", "count(*)");
    }
}


function getResellersCount($owner, $status = NULL, $type = NULL, $reseller_id = NULL)
{
    global $database;
    if ($status !== null && $status !== "") {
        if ($status == "enabled") {
            $database->where("(`status` = 1)");
        } else {
            $database->where("(`status` = 0)");
        }
    }

    if ($type !== NULL && $type !== "") {
        $group_settings = json_decode(getServerProperty('group_settings'), true);
        $database->where("member_group_id", $group_settings[$type]);
    }

    if (isAdmin($owner)) {
        if ($reseller_id !== null && $reseller_id !== "") {
            $database->where("owner_id", $reseller_id);
        }
    } else {
        $resellers = array($owner['id']);
        $allowed_resellers = array_merge($resellers, getAllResellersIdByOwnerID($owner['id']));

        if ($reseller_id !== null && $reseller_id !== "") {
            $database->where("owner_id", $reseller_id);
        } else {
            $database->where("`owner_id`", $allowed_resellers, "IN");
        }
    }

    if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
        return $database->getValue("`users`", "count(*)");
    } else {
        return $database->getValue("`reg_users`", "count(*)");
    }
}

function getClientsCountByOwnerId($userid, $status = NULL, $type = NULL, $reseller_id = NULL)
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "SELECT count(*) FROM `lines` WHERE `member_id` = :userid";
        } else {
            $sql = "SELECT count(*) FROM `users` WHERE `member_id` = :userid";
        }
        $database = $PDO->prepare($sql);
        $database->bindParam(":userid", $userid, PDO::PARAM_INT);
        $database->execute();
        return $database->fetchColumn();
    }
    return 0;
}
function getActiveCount($reseller)
{
    return isAdmin($reseller) ? getAllActiveClientsCount() : getActiveClientsCountByOwnerId($reseller["id"]);
}
function getAllActiveClientsCount()
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "SELECT count(*) FROM `lines` WHERE (`exp_date` > unix_timestamp(NOW()) OR `exp_date` IS NULL) AND `is_trial` = 0;";
        } else {
            $sql = "SELECT count(*) FROM `users` WHERE (`exp_date` > unix_timestamp(NOW()) OR `exp_date` IS NULL) AND `is_trial` = 0;";
        }
        $database = $PDO->prepare($sql);
        $database->execute();
        return $database->fetchColumn();
    }
    return 0;
}
function getActiveClientsCountByOwnerId($userid)
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "SELECT count(*) FROM `lines` WHERE `member_id` = :userid AND (`exp_date` > unix_timestamp(NOW()) OR `exp_date` IS NULL) AND `is_trial` = 0;";
        } else {
            $sql = "SELECT count(*) FROM `users` WHERE `member_id` = :userid AND (`exp_date` > unix_timestamp(NOW()) OR `exp_date` IS NULL) AND `is_trial` = 0;";
        }
        $database = $PDO->prepare($sql);
        $database->bindParam(":userid", $userid, PDO::PARAM_INT);
        $database->execute();
        return $database->fetchColumn();
    }
    return 0;
}
function getTrialClientsCount($reseller)
{
    return isAdmin($reseller) ? getAllTrialClientsCount() : getTrialClientsCountByOwnerId($reseller["id"]);
}
function getAllTrialClientsCount()
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "SELECT count(*) FROM `lines` WHERE `is_trial` = 1;";
        } else {
            $sql = "SELECT count(*) FROM `users` WHERE `is_trial` = 1;";
        }
        $database = $PDO->prepare($sql);
        $database->execute();
        return $database->fetchColumn();
    }
    return 0;
}
function getTrialClientsCountByOwnerId($userid)
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "SELECT count(*) FROM `lines` WHERE `member_id` = :userid AND `is_trial` = 1;";
        } else {
            $sql = "SELECT count(*) FROM `users` WHERE `member_id` = :userid AND `is_trial` = 1;";
        }
        $database = $PDO->prepare($sql);
        $database->bindParam(":userid", $userid, PDO::PARAM_INT);
        $database->execute();
        return $database->fetchColumn();
    }
    return 0;
}
function getNewClientsCount($reseller)
{
    return isAdmin($reseller) ? getAllNewClientsCount() : getNewClientsCountByOwnerId($reseller["id"]);
}
function getAllNewClientsCount()
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "SELECT count(*) FROM `lines` WHERE `is_trial` = 0 AND `created_at` >= unix_timestamp(NOW() - INTERVAL 7 DAY)";
        } else {
            $sql = "SELECT count(*) FROM `users` WHERE `is_trial` = 0 AND `created_at` >= unix_timestamp(NOW() - INTERVAL 7 DAY)";
        }
        $database = $PDO->prepare($sql);
        if ($database->execute()) {
            return $database->fetchColumn();
        }
    }
    return 0;
}
function getNewClientsCountByOwnerId($userid)
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "SELECT count(*) FROM `lines` WHERE `member_id` = :userid AND `is_trial` = 0 AND `created_at` >= unix_timestamp(NOW() - INTERVAL 7 DAY)";
        } else {
            $sql = "SELECT count(*) FROM `users` WHERE `member_id` = :userid AND `is_trial` = 0 AND `created_at` >= unix_timestamp(NOW() - INTERVAL 7 DAY)";
        }
        $database = $PDO->prepare($sql);
        $database->bindParam(":userid", $userid, PDO::PARAM_INT);
        if ($database->execute()) {
            return $database->fetchColumn();
        }
    }
    return 0;
}

function getActiveClientsTree($userid)
{
    $totalData = totalScreenAndClientsInTree($userid);

    $price = getUserProperty($userid, "client_price");
    $total_clients = !$totalData['total_users'] ? '0' : $totalData['total_users'];
    $total_conns = !$totalData['total_max_connections'] ? '0' : $totalData['total_max_connections'];

    if (empty($price)) {
        $price = 0;
    }

    # estimated_value = total_conns * $price;
    $estimated_value = $total_conns * $price;

    $count = [
        "total_clients" => $total_clients,
        "total_conns" => $total_conns,
        "estimated_value" => $estimated_value
    ];

    return $count;
}
function getAllChannelsCount()
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        $sql = "SELECT count(*) FROM `streams` WHERE `type` = 1;";
        $database = $PDO->prepare($sql);
        $database->execute();
        return $database->fetchColumn();
    }
    return 0;
}
function getAllVodsCount()
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        $sql = "SELECT count(*) FROM `streams` WHERE `type` = 2;";
        $database = $PDO->prepare($sql);
        $database->execute();
        return $database->fetchColumn();
    }
    return 0;
}
function getAllSeriesCount()
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "SELECT count(*) FROM `streams_series`;";
        } else {
            $sql = "SELECT count(*) FROM `series`;";
        }
        $database = $PDO->prepare($sql);
        $database->execute();
        return $database->fetchColumn();
    }
    return 0;
}
function getSalesShart($reseller)
{
    return isAdmin($reseller) ? getSalesShartByOwnerId() : getSalesShartByOwnerId($reseller["id"]);
}
function getSalesShartByOwnerId($userid = NULL)
{
    $result = array();
    $result["total"] = 0;
    $result["data"] = "";
    $PDO = getconnection();
    if ($PDO !== NULL) {
        $first_day = strtotime(date("01-m-Y"));
        $last_day = strtotime(date("t-m-Y"));
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "SELECT `created_at` FROM `lines` WHERE `is_trial` = 0 AND `created_at` >= :first_day AND `created_at` <= :last_day;";
            if ($userid) {
                $sql = "SELECT `created_at` FROM `lines` WHERE `member_id` = :userid AND `is_trial` = 0 AND `created_at` >= :first_day AND `created_at` <= :last_day;";
            }
        } else {
            $sql = "SELECT `created_at` FROM `users` WHERE `is_trial` = 0 AND `created_at` >= :first_day AND `created_at` <= :last_day;";
            if ($userid) {
                $sql = "SELECT `created_at` FROM `users` WHERE `member_id` = :userid AND `is_trial` = 0 AND `created_at` >= :first_day AND `created_at` <= :last_day;";
            }
        }
        $database = $PDO->prepare($sql);
        if ($userid) {
            $database->bindParam(":userid", $userid, PDO::PARAM_INT);
        }
        $database->bindParam(":first_day", $first_day, PDO::PARAM_INT);
        $database->bindParam(":last_day", $last_day, PDO::PARAM_INT);
        if ($database->execute()) {
            $array_result = array();
            for ($i = 0; $i < intval(date("t")); $i++) {
                $current_day = date("Y-m-d", strtotime("+" . $i . " days", $first_day));
                $array_result[$current_day] = 0;
            }
            $database_result = $database->fetchAll(PDO::FETCH_ASSOC);
            foreach ($database_result as $row) {
                $created_at = date("Y-m-d", $row["created_at"]);
                $array_result[$created_at] = !$array_result[$created_at] ? 1 : $array_result[$created_at] + 1;
                $result["total"]++;
            }
            foreach ($array_result as $day => $count) {
                $result["data"] .= "{ y: '" . $day . "', item: '" . $count . "'}, ";
            }
            $result["data"] = substr($result["data"], 0, -2);
            return $result;
        }
    }
    return $result;
}

function getExpiringClientsTable($userid, $start, $length, $search, $order_column_index, $order_type, $tree = false)
{
    $reseller = getuserbyid($userid);
    if ($reseller) {
        $columns = array(
            array("db" => "id"),
            array("db" => "display_username", "formatter" => function ($d, $row) {
                return $row["is_trial"] ? "<i class=\"fa fa-bug\" data-toggle=\"tooltip\" data-original-title=\"Sou um Teste\"></i> " . $row["username"] : $row["username"];
            }),
            array("db" => "password"), array("db" => "created_at", "formatter" => function ($d, $row) {
                return !empty($d) ? date("d/m/Y", $d) : "";
            }),
            array("db" => "exp_date", "formatter" => function ($d, $row) {
                return !empty($d) ? date("d/m/Y", $d) : "";
            }),
            array("db" => "days_to_exp", "formatter" => function ($d, $row) {
                $d1 = $row['exp_date'];
                $date_now = time();
                $days = ($d1 - $date_now) / 86400;

                if (!$row["exp_date"] || (date("d", $date_now) + intval(1)) == date("d", $d1)) {
                    return !empty($d1) ? "<span class=\"badge badge-warning\"> Amanhã (" . date("d/m/Y h:m", $d1)  . ")</span>" : "";
                    //a
                } elseif (!$row["exp_date"] || date("d", $date_now) == date("d", $d1)) {
                    return !empty($d1) ? "<span class=\"badge badge-danger\"> Hoje (" . date("d/m/Y h:m", $d1)  . ")</span>" : "";
                    //b
                } elseif ((date("d", $date_now) - intval(1)) == date("d", $d1)) {
                    return !empty($d1) ? "<span class=\"badge badge-danger\"> Ontem (" . date("d/m/Y h:m", $d1)  . ")</span>" : "";
                    //c
                } elseif (!$row["exp_date"] || $date_now < $row["exp_date"]) {
                    return !empty($d1) ? "<span class=\"badge badge-warning\">" . intval($days) . " Dias (" . date("d/m/Y h:m", $d1)  . ")</span>" : "";
                } elseif (!$row["exp_date"] || $date_now > $row["exp_date"]) {
                    return !empty($d1) ? "<span class=\"badge badge-danger\">Expirou " . date("d/m/Y h:m", $d1)  . "</span>" : "";
                }
            }),
            array("db" => "reseller_name"),
            array("db" => "max_connections"),
            array("db" => "reseller_notes", "formatter" => function ($d, $row) {
                return "<span data-toggle=\"tooltip\" data-original-title=\"" . $d . "\">" . str_limit($d, 15) . "</span>";
            }),
            array("db" => "action", "searchable" => false, "formatter" => function ($d, $row) {
                return '
				<div class="actions text-center">
                    <a href="#" class="btn btn-icon text-green btexpmessage" data-toggle="tooltip" data-original-title="Enviar aviso de expiraço" data-id="' . $row['id'] . '">
				        <i class="fab fa-whatsapp" aria-hidden="true" style="font-size: 16px"></i>
				    </a>
				    <a href="#" class="btn btn-icon text-yellow btrenewplus" data-toggle="tooltip" data-original-title="Renovar vários meses - custo depende da quantidade de meses e telas." data-id="' . $row['id'] . '" data-text="Usuario: ' . $row['username'] . '">
				        <i class="fad fa-calendar-alt" aria-hidden="true" style="font-size: 16px"></i>
				    </a>
				</div>';
            })
        );
        $clients = getAllClientsExpiringWithOptions($reseller, $start, $length, $columns, $search, $order_column_index, $order_type, $tree);
        return $clients;
    }
    return array();
}

function getExpiringP2PClientsTable($userid, $start, $length, $search, $order_column_index, $order_type, $tree = false)
{
    $reseller = getuserbyid($userid);
    if ($reseller) {
        $columns = array(
            array("db" => "id"),
            array("db" => "display_username", "formatter" => function ($d, $row) {
                return explode("@", $row["email"])[0];
            }),
            array("db" => "password", "formatter" => function ($d, $row) {
                return $row["exField3"];
            }), array("db" => "created_at", "formatter" => function ($d, $row) {
                return !empty($row["regTime"]) ? date("d/m/Y H:i", strtotime($row["regTime"])) : "-";
            }),
            array("db" => "exp_date", "formatter" => function ($d, $row) {
                return !empty($row["endTime"]) ? date("d/m/Y H:i", strtotime($row["endTime"])) : "-";
            }),
            array("db" => "days_to_exp", "formatter" => function ($d, $row) {
                $d1 = strtotime($row["endTime"]);
                $date_now = time();
                $days = ($d1 - $date_now) / 86400;

                if (!$d1 || (date("d", $date_now) + intval(1)) == date("d", $d1)) {
                    return !empty($d1) ? "<span class=\"badge badge-warning\"> Amanhã (" . date("d/m/Y h:m", $d1)  . ")</span>" : "";
                    //a
                } elseif (!$d1 || date("d", $date_now) == date("d", $d1)) {
                    return !empty($d1) ? "<span class=\"badge badge-danger\"> Hoje (" . date("d/m/Y h:m", $d1)  . ")</span>" : "";
                    //b
                } elseif ((date("d", $date_now) - intval(1)) == date("d", $d1)) {
                    return !empty($d1) ? "<span class=\"badge badge-danger\"> Ontem (" . date("d/m/Y h:m", $d1)  . ")</span>" : "";
                    //c
                } elseif (!$d1 || $date_now < $d1) {
                    return !empty($d1) ? "<span class=\"badge badge-warning\">" . intval($days) . " Dias (" . date("d/m/Y h:m", $d1)  . ")</span>" : "";
                } elseif (!$d1 || $date_now > $d1) {
                    return !empty($d1) ? "<span class=\"badge badge-danger\">Expirou " . date("d/m/Y h:m", $d1)  . "</span>" : "";
                }
            }),
            array("db" => "exField1"),
            array("db" => "reseller_notes", "formatter" => function ($d, $row) {
                return "<span data-toggle=\"tooltip\" data-original-title=\"" . $d . "\">" . str_limit($d, 15) . "</span>";
            }),
            array("db" => "action", "searchable" => false, "formatter" => function ($d, $row) {
                return '
				<div class="actions text-center">
                    <a href="#" class="btn btn-icon text-green btexpmessage" data-toggle="tooltip" data-original-title="Enviar aviso de expiraço" data-id="' . $row['id'] . '">
				        <i class="fab fa-whatsapp" aria-hidden="true" style="font-size: 16px"></i>
				    </a>
				    <a href="#" class="btn btn-icon text-yellow btrenewplus" data-toggle="tooltip" data-original-title="Renovar vários meses - custo depende da quantidade de meses e telas." data-id="' . $row['id'] . '" data-type="binstream" data-text="Usuario: ' . explode("@", $row["email"])[0] . '">
				        <i class="fad fa-calendar-alt" aria-hidden="true" style="font-size: 16px"></i>
				    </a>
				</div>';
            })
        );
        $clients = getAllClientsP2PExpiringWithOptions($reseller, $start, $length, $columns, $search, $order_column_index, $order_type, $tree);
        return $clients;
    }
    return array();
}

function getAllOnlineClientsTable($userid, $start, $length, $search_value, $order_column_index, $order_type, $p2p = false)
{
    $reseller = getuserbyid($userid);
    if ($reseller) {
        $columns = array(
            array("db" => "divergence", "formatter" => function ($d, $row) {
                if ($d <= 10) {
                    return '<i class="text-success fas fa-circle"></i>';
                } else if ($d <= 50) {
                    return '<i class="text-warning fas fa-circle"></i>';
                } else {
                    return '<i class="text-danger fas fa-circle"></i>';
                }
            }),
            array("db" => "username"),
            array("db" => "stream_name"),
            array("db" => "time", "formatter" => function ($d, $row) {
                $rTime = intval(time()) - intval($d);
                return sprintf('%02d:%02d:%02d', ($rTime / 3600), ($rTime / 60 % 60), $rTime % 60);


                //return !empty($d) ? date("d/m/Y H:i", $d) : "";
            }),
            array("db" => "user_ip"),
            array("db" => "country", "formatter" => function ($d, $row) {
                if (empty($d)) {
                    return "-";
                } else {

                    return "<img align=\"center\" src=\"https://raw.githubusercontent.com/HOSTMKBR/country-flags/main/shiny/24/" . $d . ".png\"/>";
                }
                $country = empty($d) ? "unknown" : $d;
            }), array("db" => "internet_server")
        );
        return isAdmin($reseller) ? getallonlineclientswithoptions($start, $length, $columns, $search_value, $order_column_index, $order_type, $p2p) : getallonlineclientsbyownerwithoptions($reseller, $start, $length, $columns, $search_value, $order_column_index, $order_type, $p2p);
    }
    return array();
}

function getAllResellersTable($owner_id, $start = 1, $length = 10, $search_value = "", $order_column_index = NULL, $order_type = "asc", $status = NULL, $type = NULL, $reseller_id = NULL)
{
    $owner = getuserbyid($owner_id);
    $isAdmin = isAdmin($owner);
    global $database;
    $result = array("data" => array(), "recordsTotal" => 0, "recordsFiltered" => 0);

    $result["recordsTotal"] = getResellersCount($owner, $status, $type, $reseller_id);
    $result["recordsFiltered"] = $result["recordsTotal"];

    if ($status !== null && $status !== "") {
        if ($status == "enabled") {
            $database->where("(`status` = 1)");
        } else {
            $database->where("(`status` = 0)");
        }
    }

    if ($type !== NULL && $type !== "") {
        $group_settings = json_decode(getServerProperty('group_settings'), true);
        $database->where("member_group_id", $group_settings[$type]);
    }

    if ($isAdmin) {
        if ($reseller_id !== null && $reseller_id !== "") {
            $database->where("owner_id", $reseller_id);
        }

        if (!empty($search_value)) {
            $database->where(
                "((`id` LIKE ?) OR (`username` LIKE ?) OR (`email` LIKE ?) OR (`ip` LIKE ?) OR (`notes` LIKE ?))",
                array('%' . $search_value . '%', '%' . $search_value . '%', '%' . $search_value . '%', '%' . $search_value . '%', '%' . $search_value . '%')
            );
        }
    } else {
        $resellers = array($owner_id);
        $allowed_resellers = array_merge($resellers, getAllResellersIdByOwnerID($owner_id));

        if ($reseller_id !== null && $reseller_id !== "") {
            $database->where("owner_id", $reseller_id);
        } else {
            $database->where("`owner_id`", $allowed_resellers, "IN");
        }

        if (!empty($search_value)) {
            $database->where(
                "((`id` LIKE ?) OR (`username` LIKE ?) OR (`email` LIKE ?) OR (`ip` LIKE ?) OR (`notes` LIKE ?))",
                array('%' . $search_value . '%', '%' . $search_value . '%', '%' . $search_value . '%', '%' . $search_value . '%', '%' . $search_value . '%')
            );
        }
    }

    $columns_name = ["`id`", "`username`", "`email`", "id", "`ip`", "`credits`", "`owner_id`",  "`notes`", "status"];

    // Adiciona a cláusula ORDER BY para ordenar os resultados de acordo com a coluna selecionada e o tipo de ordenação
    if ($order_column_index !== NULL &&  $order_type !== NULL && in_array($order_column_index, range(0, count($columns_name) - 1))) {

        $order_column = $columns_name[$order_column_index];
        $order_type = $order_type == "asc" ? "ASC" : "DESC";
        $database->orderBy($order_column, $order_type);
    }
    if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
        $all_resellers = $database->get("`users`", [$start, $length]);
    } else {
        $all_resellers = $database->get("`reg_users`", [$start, $length]);
    }
    // print_r($database->getLastQuery());
    // die();

    if (!empty($all_resellers)) {
        // busca os IDs dos revendedores
        $reseller_ids = array_unique(array_column($all_resellers, "owner_id"));

        // busca os nomes dos revendedores associados aos IDs
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $owners = $database->where("id", $reseller_ids, "IN")->get("users", null, ["id", "username"]);
        } else {
            $owners = $database->where("id", $reseller_ids, "IN")->get("reg_users", null, ["id", "username"]);
        }

        // cria um array associativo dos nomes dos revendedores usando o ID como chave
        $owner_names = array_column($owners, "username", "id");

        $results = array();
        foreach ($all_resellers as $current_user) {
            $current_user["reseller_name"] = isset($owner_names[$current_user["owner_id"]]) ? $owner_names[$current_user["owner_id"]] : "-";
            array_push($results, $current_user);
        }
        if (!$isAdmin) {
            //remove os cliente que o owner_id não estão na array $allowed_resellers
            $results = array_filter($results, function ($item) use ($allowed_resellers) {
                return in_array($item['owner_id'], $allowed_resellers);
            });
        }
    } else {
        $results = array();
    }

    $Allgroups = getAllGroups();
    foreach ($Allgroups as $group) {
        $group_id = $group['group_id'];
        $group_name = $group['group_name'];

        $groups[$group_id] = $group_name;
    }

    $office_groups = json_decode(getServerProperty('group_settings'), true);
    $transformedData = array_flip($office_groups);
    $transformedData = array_map('ucfirst', $transformedData);


    foreach ($results as $current_reseller) {
        $status = "";
        if ($current_reseller["status"]) {
            $status = "<span class=\"badge badge-success\">Ativo</span>";
            $message = "Bloquear";
        } else {
            $status = "<span class=\"badge badge-danger\">Desativado</span>";
            $message = "Desbloquear";
        }

        if (array_key_exists($current_reseller['member_group_id'], $transformedData)) {
            $reseller_group = "<span class=\"badge badge-success\">" . $groups[$current_reseller['member_group_id']] . "</span>";
        } else {
            $reseller_group = "<span class=\"badge badge-danger\">" . $groups[$current_reseller['member_group_id']] . "</span>";
        }

        $action = '<div class="actions text-center">';
        $action .= '<a href="/reseller/edit/' . $current_reseller['id'] . '" class="btn btn-icon text-muted" data-toggle="tooltip" data-original-title="Editar Revendedor" data-id="' . $current_reseller['id'] . '"><i class="fad fa-user-edit" aria-hidden="true" style="font-size: 16px --fa-secondary-opacity: 1.0; --fa-secondary-color: dodgerblue;"></i></a>';
        $action .= '<a href="#" class="btn btn-icon text-green btcredits" data-toggle="tooltip" data-original-title="Adic/Remover Creditos" data-id="' . $current_reseller['id'] . '" data-text="Adicionar/remover creditos do revendedor: ' . $current_reseller['username'] . '"><i class="far fa-dollar-sign" aria-hidden="true" style="font-size: 16px"></i></a>';
        $action .= '<a href="#" class="btn btn-icon text-yellow btblock" data-toggle="tooltip" data-original-title="' . $message . '" data-id="' . $current_reseller['id'] . '" data-text="' . $message . ' o revendedor: ' . $current_reseller['username'] . '"><i class="far fa-ban" aria-hidden="true" style="font-size: 16px"></i></a>';
        $action .= '<a href="#" class="btn btn-icon text-red btdelete" data-toggle="tooltip" data-original-title="Deletar Revendedor" data-id="' . $current_reseller['id'] . '" data-text="Deletar o revendedor: ' . $current_reseller['username'] . '"><i class="far fa-user-slash" aria-hidden="true" style="font-size: 16px"></i></a>';
        if ($isAdmin) {
            $action .= '<a href="#" class="btn btn-icon text-blue btresellerlogin" data-toggle="tooltip" data-original-title="Logar como revendedor" data-id="' . $current_reseller['id'] . '" data-text="Logar como revendedor: ' . $current_reseller['username'] . '"><i class="far fa-external-link" aria-hidden="true" style="font-size: 16px"></i></a>';
        }
        $action .= '</div>';

        $result["data"][] = array(
            "id" => $current_reseller["id"],
            "username" => $current_reseller["username"],
            "group" => $reseller_group,
            "email" => $current_reseller["email"],
            "ip" => $current_reseller["ip"],
            "credits" => $current_reseller['credits'],
            "last_recharge" => "",
            "reseller_name" => $current_reseller["reseller_name"],
            "notes" => "<span data-toggle=\"tooltip\" data-original-title=\"" . $current_reseller["notes"] . "\">" . str_limit($current_reseller["notes"], 15) . "</span>",
            "status" => $status,
            "action" => $action,
        );
    }
    return $result;
}

function getAllActiveClientsCountFromResellers($userid, $start, $length, $search, $order_column_index, $order_type)
{
    $reseller = getuserbyid($userid);
    if ($reseller) {
        $columns = array(
            array("db" => "id"),
            array("db" => "username"),
            array("db" => "email"),
            array("db" => "date_registered", "formatter" => function ($d, $row) {
                return !empty($d) ? date("d/m/Y", $d) : "";
            }),
            array("db" => "last_recharge", "formatter" => function ($d, $row) {
                $d1 = (int) getUserProperty($row['id'], "last_recharge");
                $d2 = strtotime("now");
                $days = ($d2 - $d1) / 86400;
                return !empty($d1) ? date("d/m/Y", $d1) . " (" . intval($days) . " Dias)" : "";
            }),
            array("db" => "ip", "formatter" => function ($d, $row) {
                return "<span data-toggle=\"tooltip\" data-original-title=\"" . $d . "\">" . str_limit($d, 15) . "</span>";
            }),
            array("db" => "credits"),
            array("db" => "notes", "formatter" => function ($d, $row) {
                return "<span data-toggle=\"tooltip\" data-original-title=\"" . $d . "\">" . str_limit($d, 15) . "</span>";
            }),
            array("db" => "reseller_name"),
            array("db" => "status", "formatter" => function ($d, $row) {
                return $row["status"] ? "<span class=\"badge badge-success\">Ativo</span>" : "<span class=\"badge badge-danger\">Bloqueado</span>";
            }),
            array("db" => "action", "searchable" => false, "formatter" => function ($d, $row) {
                return '
				<div class="actions text-center">
				    <a href="/reseller/edit/' . $row['id'] . '" class="btn btn-icon text-muted" data-toggle="tooltip" data-original-title="Editar Revendedor" data-id="' . $row['id'] . '">
				        <i class="fas fa-user-edit" aria-hidden="true" style="font-size: 16px"></i>
				    </a>
				    <a href="#" class="btn btn-icon text-green btcredits" data-toggle="tooltip" data-original-title="Adic/Remover Creditos" data-id="' . $row['id'] . '" data-text="Adicionar/remover creditos do revendedor: ' . $row['username'] . '">
				        <i class="fas fa-dollar-sign" aria-hidden="true" style="font-size: 16px"></i>
				    </a>
				    <a href="#" class="btn btn-icon text-yellow btblock" data-toggle="tooltip" data-original-title="Bloquear/Desbloquear" data-id="' . $row['id'] . '" data-text="Bloquear/desbloquear o revendedor: ' . $row['username'] . '">
				        <i class="fa fa-ban" aria-hidden="true" style="font-size: 16px"></i>
				    </a>
				    <a href="#" class="btn btn-icon text-red btdelete" data-toggle="tooltip" data-original-title="Deletar Revendedor" data-id="' . $row['id'] . '" data-text="Deletar o revendedor: ' . $row['username'] . '">
				        <i class="fas fa-user-slash" aria-hidden="true" style="font-size: 16px"></i>
				    </a>
				</div>
				';
            })
        );
        $resellers = getallresellersbyownerwithoptions($reseller, $start, $length, $columns, $search, $order_column_index, $order_type);
        return $resellers;
    }
    return array();
}

function getTickets($userid, $start, $length, $search, $order_column_index, $order_type)
{
    $reseller = getuserbyid($userid);
    if ($reseller) {
        $columns = array(
            array("db" => "id"),
            array("db" => "reseller"),
            array("db" => "title"),
            array("db" => "last_reply"),
            array("db" => "status", "formatter" => function ($d, $row) {
                return $row["status"] ? "<span class=\"badge badge-success\">Aberto</span>" : "<span class=\"badge badge-danger\">Fechado</span>";
            }),
            array("db" => "action", "searchable" => false, "formatter" => function ($d, $row) {
                return "<div class=\"actions text-center\">\r\n<a href=\"./ticket.php?ticket_id=" . $row["id"] . "\" class=\"btn btn-icon text-muted\" data-toggle=\"tooltip\" data-original-title=\"Ver Ticket\" data-id=\"" . $row["id"] . "\">\r\n                                    <i class=\"fa fa-search\" aria-hidden=\"true\" style=\"font-size: 16px\"></i>\r\n                                    </a>\r\n                                    <a href=\"#\" class=\"btn btn-icon text-yellow bttoggle\" data-toggle=\"tooltip\" data-original-title=\"Abrir/Fechar\" data-id=\"" . $row["id"] . "\" data-text=\"Abrir/Fechar o ticket\">\r\n                                    <i class=\"fa fa-ban\" aria-hidden=\"true\" style=\"font-size: 16px\"></i>\r\n                                    </a>\r\n                                </div>";
            })
        );
        $columns_admin = array(array("db" => "id"), array("db" => "reseller"), array("db" => "title"), array("db" => "last_reply"), array("db" => "status", "formatter" => function ($d, $row) {
            return $row["status"] ? "<span class=\"badge badge-success\">Aberto</span>" : "<span class=\"badge badge-danger\">Fechado</span>";
        }), array("db" => "action", "searchable" => false, "formatter" => function ($d, $row) {
            return "<div class=\"actions text-center\">\r\n                                    <a href=\"/ticket/view/" . $row["id"] . "\" class=\"btn btn-icon text-muted\" data-toggle=\"tooltip\" data-original-title=\"Ver Ticket\" data-id=\"" . $row["id"] . "\">\r\n                                    <i class=\"fa fa-search\" aria-hidden=\"true\" style=\"font-size: 16px\"></i>\r\n                                    </a>\r\n                                    <a href=\"#\" class=\"btn btn-icon text-yellow bttoggle\" data-toggle=\"tooltip\" data-original-title=\"Abrir/Fechar\" data-id=\"" . $row["id"] . "\" data-text=\"Abrir/Fechar o ticket\">\r\n                                    <i class=\"fa fa-ban\" aria-hidden=\"true\" style=\"font-size: 16px\"></i>\r\n                                    </a>\r\n                                    <a href=\"#\" class=\"btn btn-icon text-red btdelete\" data-toggle=\"tooltip\" data-original-title=\"Deletar Ticket\" data-id=\"" . $row["id"] . "\" data-text=\"Deletar o ticket\">\r\n                                    <i class=\"fa fa-trash\" aria-hidden=\"true\" style=\"font-size: 16px\"></i>\r\n                                    </a>\r\n                                </div>";
        }));
        $tickets = isAdmin($reseller) ? getallticketsadminwithoptions($start, $length, $columns_admin, $search, $order_column_index, $order_type) : getallticketsbyownerwithoptions($reseller, $start, $length, $columns, $search, $order_column_index, $order_type);
        return $tickets;
    }
    return array();
}
function updateClient($client_id, $username, $password, $phone, $email, $reseller_notes, $bouquet, $max_connections = "", $exp_date = "")
{
    global $database;
    $data = array(
        "username" => $username,
        "password" => $password,
        "phone" => $phone,
        "email" => $email,
        "reseller_notes" => $reseller_notes,
        "bouquet" => $bouquet
    );
    if (!empty($max_connections)) {
        $data["max_connections"] = $max_connections;
    }
    if (!empty($exp_date)) {
        $data["exp_date"] = $exp_date;
    }

    $database->where("id", $client_id);
    if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
        return $database->update("`lines`", $data);
    } else {
        return $database->update("`users`", $data);
    }
    return false;
}

function renewClient($client_id, $month, $type = "")
{
    if ($type == "binstream") {
        include_once(__DIR__ . "/class/binstream.php");
        $binstream = new BinStream();
        $client = $binstream->getUser($client_id);
        $client['endTime'] = is_null($client['endTime']) ? date("Y-m-d\TH:i:s\Z") : $client['endTime'];
        if ($client) {
            $endTime = time() < strtotime($client["endTime"]) ? strtotime($client["endTime"]) : time();

            $exField4 = json_decode($client['exField4'], true);
            $trust_renew = isset($exField4["trust_renew"]) ? $exField4["trust_renew"] : 0;
            if ($trust_renew > 0) {
                $endTime = strtotime("-" . $trust_renew . " day", $endTime);
            }
            $exField4['trust_renew'] = 0;

            $data = [
                'endTime' => gmdate("Y-m-d\TH:i:s\Z", strtotime("+" . $month . " month", $endTime)),
                'status' => 1,
                'type' => 1,
                'exField4' => json_encode($exField4)
            ];
            $result = $binstream->updateUser($client_id, $data);
            if ($result['id']) {
                return true;
            }
        }
    } else {
        $client = getclientbyid($client_id);
        if ($client) {

            $exp_date = time() < $client["exp_date"] ? $client["exp_date"] : time();
            $exp_date = strtotime("+" . $month . " month", $exp_date);

            $trust_renew = isset($client["trust_renew"]) ? $client["trust_renew"] : 0;
            if ($trust_renew > 0) {
                $exp_date = strtotime("-" . $trust_renew . " day", $exp_date);
            }
            $PDO = getconnection();
            if ($PDO !== NULL) {
                if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
                    $sql = "UPDATE `lines` SET `exp_date` = :exp_date, `is_trial` = '0', `trust_renew` = 0 WHERE `id` = :client_id LIMIT 1;";
                } else {
                    $sql = "UPDATE `users` SET `exp_date` = :exp_date, `is_trial` = '0', `trust_renew` = 0 WHERE `id` = :client_id LIMIT 1;";
                }
                $database = $PDO->prepare($sql);
                $database->bindParam(":exp_date", $exp_date, PDO::PARAM_INT);
                $database->bindParam(":client_id", $client_id, PDO::PARAM_INT);
                if ($database->execute()) {
                    return true;
                }
            }
        }
    }
    return false;
}

function trustRenewClient($client_id, $type = "")
{
    if ($type == "binstream") {
        if (getServerProperty("binstream_trust_renew_status", 0) == 0) {
            return ['error' => 'Renovação de confiança desativada pela administração.'];
        }
        include_once(__DIR__ . "/class/binstream.php");
        $binstream = new BinStream();
        $client = $binstream->getUser($client_id);
        $client['endTime'] = is_null($client['endTime']) ? date("Y-m-d\TH:i:s\Z") : $client['endTime'];
        if ($client) {
            if ($client['type'] == 0) {
                return ['error' => 'Não é possível realizar renovação de confiança em usuário teste.'];
            }
            $exField4 = json_decode($client['exField4'], true);
            if (isset($exField4['trust_renew']) && $exField4['trust_renew'] > 0) {
                return ['error' => 'O usuário já tem uma renovação de confiança.'];
            }
            if (($client['status'] == 0) || ($client['status'] == -1)) {
                return ['error' => 'Não é possível renovar um usuário desativado.'];
            }

            $days = getServerProperty("binstream_trust_renew_time", 3);
            $endTime = time() < strtotime($client["endTime"]) ? strtotime($client["endTime"]) : time();

            $exField4 = json_decode($client['exField4'], true);
            $exField4['trust_renew'] = $days;

            $data = [
                'endTime' => gmdate("Y-m-d\TH:i:s\Z", strtotime("+" . $days . " day", $endTime)),
                'status' => 1,
                'exField4' => json_encode($exField4)
            ];
            $result = $binstream->updateUser($client_id, $data);
            if ($result['id']) {
                return true;
            }
        }
    } else {
        if (getServerProperty("iptv_trust_renew_status", 0) == 0) {
            return ['error' => 'Renovação de confiança desativada pela administração.'];
        }
        $client = getclientbyid($client_id);
        if ($client) {
            if ($client['is_trial'] == 1) {
                return ['error' => 'Não é possível realizar renovação de confiança em usuário teste.'];
            }
            if ($client['trust_renew'] > 0) {
                return ['error' => 'O usuário já tem uma renovação de confiança.'];
            }
            if (($client['enabled'] != 1) || ($client['admin_enabled'] != 1)) {
                return ['error' => 'Não é possível renovar um usuário desativado.'];
            }

            $days = getServerProperty("iptv_trust_renew_time", 3);
            $exp_date = time() < $client["exp_date"] ? $client["exp_date"] : time();
            $exp_date = strtotime("+" . $days . " day", $exp_date);
            $PDO = getconnection();
            if ($PDO !== NULL) {
                if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
                    $sql = "UPDATE `lines` SET `exp_date` = :exp_date, `is_trial` = '0', `trust_renew` = :trust_days WHERE `id` = :client_id LIMIT 1;";
                } else {
                    $sql = "UPDATE `users` SET `exp_date` = :exp_date, `is_trial` = '0', `trust_renew` = :trust_days WHERE `id` = :client_id LIMIT 1;";
                }
                $database = $PDO->prepare($sql);
                $database->bindParam(":exp_date", $exp_date, PDO::PARAM_INT);
                $database->bindParam(":client_id", $client_id, PDO::PARAM_INT);
                $database->bindParam(":trust_days", $days, PDO::PARAM_STR, 255);
                if ($database->execute()) {
                    return true;
                }
            }
        }
    }
    return ['error' => 'Não foi possível realizar a renovação de confiança.'];
}

function addScreenClient($client_id, $max_connections)
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "UPDATE `lines` SET `max_connections` = `max_connections` + :max_connections WHERE `id` = :client_id LIMIT 1;";
        } else {
            $sql = "UPDATE `users` SET `max_connections` = `max_connections` + :max_connections WHERE `id` = :client_id LIMIT 1;";
        }
        $database = $PDO->prepare($sql);
        $database->bindParam(":max_connections", $max_connections, PDO::PARAM_INT);
        $database->bindParam(":client_id", $client_id, PDO::PARAM_INT);
        if ($database->execute()) {
            return true;
        }
    }
}
function addOrRemoveCredits($user_id, $credits)
{
    $reseller = getuserbyid($user_id);
    if ($reseller) {
        if (isAdmin($reseller) || isPartner($reseller)) {
            return true;
        }
        if (0 <= $reseller["credits"] + $credits) {
            $PDO = getconnection();
            if ($PDO !== NULL) {
                if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
                    $sql = "UPDATE `users` SET `credits` = `credits` + :credits WHERE `id` = :user_id LIMIT 1;";
                } else {
                    $sql = "UPDATE `reg_users` SET `credits` = `credits` + :credits WHERE `id` = :user_id LIMIT 1;";
                }
                $database = $PDO->prepare($sql);
                $database->bindParam(":credits", $credits, PDO::PARAM_STR);
                $database->bindParam(":user_id", $user_id, PDO::PARAM_INT);
                if ($database->execute() && 0 < $database->rowCount()) {
                    return true;
                }
            }
        }
    }
    return false;
}
function transferCredits($from_id, $to_id, $credits)
{
    $from_reseller = getuserbyid($from_id);
    $to_reseller = getuserbyid($to_id);
    if ($from_reseller && $to_reseller) {
        if (isAdmin($to_reseller)) {
            return true;
        }

        $from_credits = $from_reseller['credits'] - $credits;
        $to_credits = $to_reseller['credits'] + $credits;
        if (((0 <= $from_credits) || (isAdmin($from_reseller) || isPartner($from_reseller))) && (0 <= $to_credits)) {
            $PDO = getconnection();

            if ($PDO !== NULL) {
                if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
                    $sql = 'UPDATE `users` SET `credits` = :credits WHERE `id` = :user_id LIMIT 1;';
                } else {
                    $sql = 'UPDATE `reg_users` SET `credits` = :credits WHERE `id` = :user_id LIMIT 1;';
                }
                $database = $PDO->prepare($sql);
                $from_result = false;
                $to_result = false;

                if (!isAdmin($from_reseller)) {
                    $database->bindParam(':credits', $from_credits, PDO::PARAM_INT);
                    $database->bindParam(':user_id', $from_reseller['id'], PDO::PARAM_INT);

                    if ($database->execute()) {
                        if (0 < $database->rowCount()) {
                            $from_result = true;
                        }
                    }
                } else {
                    $from_result = true;
                }

                $database->bindParam(':credits', $to_credits, PDO::PARAM_INT);
                $database->bindParam(':user_id', $to_reseller['id'], PDO::PARAM_INT);

                if ($database->execute()) {
                    if (0 < $database->rowCount()) {
                        $to_result = true;
                    }
                }

                if ($from_result && $to_result) {
                    return true;
                }
            }
        }
    }

    return false;
}

function toggleBlock($user_id, $allBelow = false, $blockClients = false)
{
    global $database;
    if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
        $reseller_table = "`users`";
        $client_table = "`lines`";
    } else {
        $reseller_table = "`reg_users`";
        $client_table = "`users`";
    }

    $resellers = array($user_id);
    if ($allBelow) {
        $resellers = array_merge($resellers, getAllResellersIdByOwnerID($user_id));
    }
    $user = $database->where("id", $user_id)->getOne($reseller_table, ["id", "status"]);
    if ($user) {
        $status['status'] = !$user['status'];
        $result = $database->where("id", $resellers, "IN")->update($reseller_table, $status);
        if ($result) {
            if ($blockClients) {
                $client_status['enabled'] = $status['status'];
                $clients = array_column($database->where("member_id", $resellers, "IN")->get($client_table, null, ["id"]), "id");
                $database->where("id", $clients, "IN")->update($client_table, $client_status);
            }
            return true;
        }
    }
    return false;
}

function toggleClientBlock($user_id, $type)
{
    if ($type == "binstream") {
        include_once(__DIR__ . "/class/binstream.php");
        $binstream = new BinStream();
        $client = $binstream->getUser($user_id);
        if ($client) {
            if (!is_null($client["endTime"]) && strtotime($client["endTime"]) < time()) {
                if ($client['status'] == 1) {
                    $client['status'] = 0;
                    $result = $binstream->updateUser($user_id, $client);
                }
                return false;
            }
            $client['status'] = !$client['status'];
            $result = $binstream->updateUser($user_id, $client);
            if ($result['id']) {
                return true;
            }
        }
    }
    $PDO = getconnection();
    if ($PDO !== NULL) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "UPDATE `lines` SET `enabled` = !`enabled` WHERE `id` = :user_id LIMIT 1;";
        } else {
            $sql = "UPDATE `users` SET `enabled` = !`enabled` WHERE `id` = :user_id LIMIT 1;";
        }
        $database = $PDO->prepare($sql);
        $database->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        if ($database->execute()) {
            return true;
        }
    }
    return false;
}
function deleteClient($user_id, $type = "")
{
    if ($type == "binstream") {
        include_once(__DIR__ . "/class/binstream.php");
        $binstream = new BinStream();
        return $binstream->deleteUser($user_id);
    } else {
        $PDO = getconnection();
        if ($PDO !== NULL) {
            if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
                $sql = "DELETE FROM `lines` WHERE `id` = :user_id LIMIT 1;";
            } else {
                $sql = "DELETE FROM `users` WHERE `id` = :user_id LIMIT 1;";
            }
            $database = $PDO->prepare($sql);
            $database->bindParam(":user_id", $user_id, PDO::PARAM_INT);
            if ($database->execute()) {
                return true;
            }
        }
    }
    return false;
}
function getGroupByID($group_id)
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "SELECT * FROM `users_groups` WHERE `group_id` = :group_id LIMIT 1;";
        } else {
            $sql = "SELECT * FROM `member_groups` WHERE `group_id` = :group_id LIMIT 1;";
        }
        $database = $PDO->prepare($sql);
        $database->bindParam(":group_id", $group_id, PDO::PARAM_INT);
        $database->execute();
        return $database->fetch(PDO::FETCH_ASSOC);
    }
    return false;
}
function getBouquets()
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        $sql = "SELECT * FROM `bouquets`;";
        $database = $PDO->prepare($sql);
        $database->execute();
        return $database->fetchAll(PDO::FETCH_ASSOC);
    }
    return array();
}
function getAllGroups()
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "SELECT * FROM `users_groups`;";
        } else {
            $sql = "SELECT * FROM `member_groups`;";
        }
        $database = $PDO->prepare($sql);
        $database->execute();
        return $database->fetchAll(PDO::FETCH_ASSOC);
    }
    return array();
}
function getPackageByID($package_id)
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "SELECT * FROM `users_packages` WHERE `id` = :package_id LIMIT 1;";
        } else {
            $sql = "SELECT * FROM `packages` WHERE `id` = :package_id LIMIT 1;";
        }
        $database = $PDO->prepare($sql);
        $database->bindParam(":package_id", $package_id, PDO::PARAM_INT);
        if ($database->execute()) {
            return $database->fetch(PDO::FETCH_ASSOC);
        }
    }
    return false;
}
function getPackages($byPassCache = false)
{
    global $redis;
    $key = OFFICE_CONFIG['panel_id'] . "_xtream_packages";
    $cached_value = $redis->get($key);
    if ($cached_value !== false && !$byPassCache) {
        return json_decode($cached_value, true);
    }

    $PDO = getconnection();
    if ($PDO !== NULL) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "SELECT * FROM `users_packages`;";
        } else {
            $sql = "SELECT * FROM `packages`;";
        }
        $database = $PDO->query($sql);
        $result = $database->fetchAll(PDO::FETCH_ASSOC);
        $redis->setex($key, 3600, json_encode($result)); //3600 = 1 hour

        return $result;
    }
    return array();
}

function getNewChannels($limit = 10)
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        $sql = "SELECT `category_id`, `stream_display_name`, `stream_icon`, `added` FROM `streams` WHERE `type` = 1 OR `type` = 3 ORDER BY `added` DESC LIMIT :_limit;";
        $database = $PDO->prepare($sql);
        $database->bindParam(":_limit", $limit, PDO::PARAM_INT);
        if ($database->execute()) {
            return $database->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    return array();
}

function getNewMovies($limit = 10)
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "SELECT `category_id`, `stream_display_name`, `movie_properties`, `added` FROM `streams` WHERE `type` = 2 ORDER BY `added` DESC LIMIT :_limit;";
        } else {
            $sql = "SELECT `category_id`, `stream_display_name`, `movie_propeties`, `added` FROM `streams` WHERE `type` = 2 ORDER BY `added` DESC LIMIT :_limit;";
        }
        $database = $PDO->prepare($sql);
        $database->bindParam(":_limit", $limit, PDO::PARAM_INT);
        if ($database->execute()) {
            return $database->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    return array();
}

function getNewSeries($limit = 10)
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "SELECT `category_id`, `title`, `cover`, `last_modified` FROM `streams_series` ORDER BY `id` DESC LIMIT :_limit;";
        } else {
            $sql = "SELECT `category_id`, `title`, `cover`, `last_modified` FROM `series` ORDER BY `id` DESC LIMIT :_limit;";
        }
        $database = $PDO->prepare($sql);
        $database->bindParam(":_limit", $limit, PDO::PARAM_INT);
        if ($database->execute()) {
            return $database->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    return array();
}

function getCategoryNameById($category_id, $categories)
{
    if (!isset($categories) || isset($categories) && empty($categories)) {
        $categories = getAllCategories();
    }

    foreach ($categories as $category) {
        if ($category['id'] == $category_id) {
            return "{$category['category_name']}";
        }
    }
    return false;
}

function getAllCategories()
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = 'SELECT `id`, `category_name` FROM `streams_categories`;';
        } else {
            $sql = 'SELECT `id`, `category_name` FROM `stream_categories`;';
        }
        $database = $PDO->prepare($sql);
        if ($database->execute()) {
            $results = $database->fetchAll();
            return $results;
        }
    }
    return false;
}

function isAdmin($user)
{
    $group_settings = json_decode(getServerProperty("group_settings"), true);
    return $group_settings["admin"] == $user["member_group_id"];
}
function isPartner($user)
{
    $group_settings = json_decode(getServerProperty("group_settings"), true);
    return $group_settings["partner"] == $user["member_group_id"];
}
function isUltra($user)
{
    $group_settings = json_decode(getServerProperty("group_settings"), true);
    return $group_settings["ultra"] == $user["member_group_id"];
}
// function isMaster($user)
// {
//     $group_settings = json_decode(getServerProperty("group_settings"), true);
//     if ($group_settings["master"] == $user["member_group_id"]) {
//         $owner_id = $user["owner_id"];
//         if ($owner_id) {
//             $owner = getuserbyid($owner_id);
//             if ($owner && $group_settings["master"] == $owner["member_group_id"]) {
//                 return false;
//             }
//         }
//         return true;
//     }
//     return false;
// }

function isMaster($user)
{
    $group_settings = json_decode(getServerProperty("group_settings"), true);
    return $group_settings["master"] == $user["member_group_id"];
}

function isReseller($user)
{
    $group_settings = json_decode(getServerProperty("group_settings"), true);
    return $group_settings["reseller"] == $user["member_group_id"];
}
function hasPermission($user_id, $client_id, $type = "")
{
    $owner = getuserbyid($user_id);
    if ($owner) {
        if (isadmin($owner)) {
            return true;
        }
        if ($type == "binstream") {
            include_once(__DIR__ . "/class/binstream.php");
            $binStream = new BinStream();
            $client = $binStream->getuser($client_id);
            if ($client) {
                $reseller = getuserbyid(intval($client["exField2"]));
                while ($reseller && $reseller["id"] != $user_id) {
                    $reseller = getuserbyid($reseller["owner_id"]);
                }
                return $reseller;
            }
        } else {
            $client = getclientbyid($client_id);
            if ($client) {
                $reseller = getuserbyid($client["member_id"]);
                while ($reseller && $reseller["id"] != $user_id) {
                    $reseller = getuserbyid($reseller["owner_id"]);
                }
                return $reseller;
            }
        }
    }
    return false;
}

function masterHasPermission($master_id, $reseller_id)
{
    $owner = getuserbyid($master_id);
    if ($owner && isadmin($owner)) {
        return true;
    }
    $reseller = getuserbyid($reseller_id);
    if ($reseller) {
        $resellers = array($master_id);
        $resellers = array_merge($resellers, getAllResellersIdByOwnerID($master_id));
        if (in_array($reseller['owner_id'], $resellers)) {
            return true;
        }
    }
    return false;
}

function createTicket($logged_user, $reseller_id, $title, $message)
{
    $member_id = $logged_user["id"];
    $admin_read = 0;
    $user_read = 1;
    if (isadmin($logged_user)) {
        $member_id = $reseller_id;
        $admin_read = 1;
        $user_read = 0;
    }
    $ticket_id = insertTicket($member_id, $title, 1, $admin_read, $user_read);
    if ($ticket_id !== false && insertTicketReply($ticket_id, $admin_read, $message)) {
        return $ticket_id;
    }
    return false;
}
function insertTicket($member_id, $title, $status, $admin_read, $user_read)
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        $sql = "INSERT INTO `tickets` (`id`, `member_id`, `title`, `status`, `admin_read`, `user_read`) VALUES (NULL, :member_id, :title, :status, :admin_read, :user_read)";
        $database = $PDO->prepare($sql);
        $database->bindParam(":member_id", $member_id, PDO::PARAM_INT);
        $database->bindParam(":title", $title, PDO::PARAM_STR, 255);
        $database->bindParam(":status", $status, PDO::PARAM_INT);
        $database->bindParam(":admin_read", $admin_read, PDO::PARAM_INT);
        $database->bindParam(":user_read", $user_read, PDO::PARAM_INT);
        if ($database->execute()) {
            return $PDO->lastInsertId();
        }
    }
    return false;
}
function insertTicketReply($ticket_id, $admin_reply, $message)
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        $sql = "INSERT INTO `tickets_replies` (`id`, `ticket_id`, `admin_reply`, `message`, `date`) VALUES (NULL, :ticket_id, :admin_reply, :message, unix_timestamp(NOW()))";
        $database = $PDO->prepare($sql);
        $database->bindParam(":ticket_id", $ticket_id, PDO::PARAM_INT);
        $database->bindParam(":admin_reply", $admin_reply, PDO::PARAM_INT);
        $database->bindParam(":message", $message, PDO::PARAM_STR, 1000);
        if ($database->execute()) {
            $other_person = $admin_reply ? "user" : "admin";
            updatereadticket($ticket_id, $other_person, 0);
            return true;
        }
    }
    return false;
}
function getTicketReplies($ticket_id)
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        $sql = "SELECT * FROM `tickets_replies` WHERE `ticket_id` = :ticket_id ORDER BY `id` DESC;";
        $database = $PDO->prepare($sql);
        $database->bindParam(":ticket_id", $ticket_id, PDO::PARAM_INT);
        if ($database->execute()) {
            return $database->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    return array();
}
function resetPassword($email)
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 2;
    }
    $user = getuserbyemail($email);
    if ($user) {
        $reset_key = getRandomMD5($user["id"]);
        deleteUserProperty($user["id"], "reset_key");
        if (addUserProperty($user["id"], "reset_key", $reset_key)) {
            $reset_link = getBaseURL() . "reset_password.php?key=" . $reset_key;
            $email_settings = json_decode(getServerProperty("email_settings"), true);
            $sender_name = $email_settings["sender_name"];
            $sender_email = $email_settings["sender_email"];
            $email_messages = json_decode(getServerProperty("email_messages"), true);
            $server_name = getServerProperty("server_name");
            $pass_recovery_subject = str_replace(array("#username#", "#server_name#"), array($user["username"], $server_name), $email_messages["pass_recovery_subject"]);
            $pass_recovery_message = str_replace(array("#username#", "#server_name#", "#reset_link#"), array($user["username"], $server_name, $reset_link), $email_messages["pass_recovery_message"]);
            if (smtpmailer($email, $pass_recovery_subject, $pass_recovery_message)) {
                return 1;
            }
        }
        return 4;
    }
    return 3;
}
function addUserProperty($userid, $property, $value)
{
    global $redis;
    $key = OFFICE_CONFIG['panel_id'] . "_userid_" . $userid . "_property_" . $property;


    $PDO = getofficeconnection();
    if ($PDO !== NULL) {
        $sql = "INSERT INTO `user_properties` (`userid`, `property`, `value`) VALUES (:userid, :property, :_value);";
        $database = $PDO->prepare($sql);
        $database->bindParam(":userid", $userid, PDO::PARAM_INT);
        $database->bindParam(":property", $property, PDO::PARAM_STR, 255);
        $database->bindParam(":_value", $value, PDO::PARAM_STR, 10000);
        if ($database->execute()) {
            $redis->set($key, $value);
            $redis->expire($key, 3600); //300 = 5 minutos
            return true;
        }
    }
    return false;
}

function getUserProperty($userid, $property, $default_value = "", $byPassCache = false)
{
    global $redis;

    // Define a chave do cache
    $key = OFFICE_CONFIG['panel_id'] . "_userid_" . $userid . "_property_" . $property;

    // Verificando se o valor está em cache
    $cached_value = $redis->get($key);
    if ($cached_value !== false && !$byPassCache) {
        // Retornando valor em cache
        return $cached_value;
    } else {
        // Conectando ao banco de dados
        global $databaseOffice;

        $databaseOffice->where("userid", $userid);
        $databaseOffice->where("property", $property);
        $result = $databaseOffice->getOne("user_properties");

        if ($databaseOffice->count > 0) {
            // Armazena o valor em cache
            $redis->set($key, $result["value"]);
            $redis->expire($key, 3600); //300 = 5 minutos
            return $result["value"];
        }
    }
    // Retorna o valor padrão
    return $default_value;
}

function getUserPropertyDecode($userid, $property, $default_value = "")
{
    return json_decode(getUserProperty($userid, $property, $default_value = "", true), true);
}

function updateUserProperty($userid, $property, $value)
{
    if (getUserProperty($userid, $property, NULL) !== NULL) {
        $PDO = getofficeconnection();
        if ($PDO !== NULL) {
            $sql = "UPDATE `user_properties` SET `value`= :_value WHERE `userid` = :userid AND `property` = :property ;";
            $database = $PDO->prepare($sql);
            $database->bindParam(":userid", $userid, PDO::PARAM_INT);
            $database->bindParam(":property", $property, PDO::PARAM_STR, 255);
            $database->bindParam(":_value", $value, PDO::PARAM_STR, 10000);
            if ($database->execute()) {
                return true;
            }
        }
        return false;
    }
    return addUserProperty($userid, $property, $value);
}

function getCreditsByUser($user)
{
    if (isAdmin($user) || isPartner($user)) {
        return '&#8734;';
    } else {
        $reseller = getUserByID($user['id']);
        return $reseller['credits'];
    }
}
function getUserPropertyByValue($property, $value, $default_value = "")
{
    $PDO = getofficeconnection();
    if ($PDO !== NULL) {
        $sql = "SELECT * FROM `user_properties` WHERE `value` = :value AND `property` = :property LIMIT 1;";
        $database = $PDO->prepare($sql);
        $database->bindParam(":value", $value, PDO::PARAM_STR, 10000);
        $database->bindParam(":property", $property, PDO::PARAM_STR, 255);
        if ($database->execute()) {
            return $database->fetch(PDO::FETCH_ASSOC);
        }
    }
    return $default_value;
}
function deleteUserProperty($userid, $property)
{
    $PDO = getofficeconnection();
    if ($PDO !== NULL) {
        $sql = "DELETE FROM `user_properties` WHERE `userid` = :userid AND `property` = :property LIMIT 1;";
        $database = $PDO->prepare($sql);
        $database->bindParam(":userid", $userid, PDO::PARAM_INT);
        $database->bindParam(":property", $property, PDO::PARAM_STR, 255);
        if ($database->execute()) {
            clearUserCache($userid["id"], $property);
            return true;
        }
    }
    return false;
}
function deleteAllUserProperty($userid)
{
    $PDO = getofficeconnection();
    if ($PDO !== NULL) {
        $sql = "DELETE FROM `user_properties` WHERE `userid` = :userid;";
        $database = $PDO->prepare($sql);
        $database->bindParam(":userid", $userid, PDO::PARAM_INT);
        if ($database->execute()) {
            return true;
        }
    }
    return false;
}
// End Reg_users properties

function addServerProperty($property, $value)
{
    $PDO = getofficeconnection();
    if ($PDO !== NULL) {
        $sql = "INSERT INTO `office_properties` (`property`, `value`) VALUES (:property, :_value);";
        $database = $PDO->prepare($sql);
        $database->bindParam(":property", $property, PDO::PARAM_STR, 255);
        $database->bindParam(":_value", $value, PDO::PARAM_STR, 10000);
        if ($database->execute()) {
            return true;
        }
    }
    return false;
}
function getServerProperties()
{
    $PDO = getofficeconnection();
    if ($PDO !== NULL) {
        $sql = "SELECT `property`, `value` FROM `office_properties`;";
        $database = $PDO->prepare($sql);
        if ($database->execute()) {
            return $database->fetchAll(PDO::FETCH_KEY_PAIR);
        }
    }
    return array();
}

function getServerProperty($property, $default_value = "", $byPassCache = false)
{
    global $redis;

    // Verificando se o valor está em cache
    $key = OFFICE_CONFIG['panel_id'] . "_server_property_" . $property;
    $cached_value = $redis->get($key);
    if ($cached_value !== false && !$byPassCache) {
        // Retornando valor em cache
        return $cached_value;
    } else {
        // Conectando ao banco de dados
        global $databaseOffice;

        $databaseOffice->where("property", $property);
        $result = $databaseOffice->getOne("office_properties");
        if ($databaseOffice->count > 0) {
            $redis->set($key, $result['value']);
            $redis->expire($key, 28800); //28800 = 8 horas
            return $result['value'];
        }
    }
    return $default_value;
}
function getServerPropertyDecode($property, $default_value = "")
{
    return json_decode(getServerProperty($property, $default_value), true);
}

function updateServerProperty($property, $value)
{
    if (getserverproperty($property, NULL) !== NULL) {
        $PDO = getofficeconnection();
        if ($PDO !== NULL) {
            $sql = "UPDATE `office_properties` SET `value` = :_value WHERE `property` = :property LIMIT 1;";
            $database = $PDO->prepare($sql);
            $database->bindParam(":property", $property, PDO::PARAM_STR, 255);
            $database->bindParam(":_value", $value, PDO::PARAM_STR, 10000);
            if ($database->execute()) {
                return true;
            }
        }
        return false;
    }
    return addserverproperty($property, $value);
}
function deleteServerProperty($property)
{
    $PDO = getofficeconnection();
    if ($PDO !== NULL) {
        $sql = "DELETE FROM `office_properties` WHERE `property` = :property LIMIT 1;";
        $database = $PDO->prepare($sql);
        $database->bindParam(":property", $property, PDO::PARAM_STR, 255);
        if ($database->execute()) {
            return true;
        }
    }
    return false;
}
function getServerSettings()
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        $sql = "SELECT * FROM `settings`;";
        $database = $PDO->prepare($sql);
        $database->execute();
        return $database->fetch(PDO::FETCH_ASSOC);
    }
    return array();
}
function getTestUrl($userid, $type = "iptv")
{
    $test_keys = array(
        "iptv" => "test_key",
        "binstream" => "test_key_p2p",
        "code" => "test_key_code"
    );
    $key_name = $test_keys[$type] ?? $test_keys["iptv"];
    $result = getuserproperty($userid, $key_name);
    if (!$result) {
        $result = generate_uuid();
        if (!adduserproperty($userid, $key_name, $result)) {
            return "You dont have a test url :c";
        }
    }
    return getBaseURL() . "test/" . $result;
}

function getChatbotUrl($userid)
{
    $result = getuserproperty($userid, "chatbot_token");
    if (!$result) {
        $result = generate_uuid();
        if (!adduserproperty($userid, "chatbot_token", $result)) {
            return "You dont have a chatbot key";
        }
    }
    return getBaseURL() . $result;
}

function insertRegUserLog($owner, $username, $password, $text, $type = "", $action = "", $log_id = 0, $package = 0, $cost = 0, $credits_after = 0, $converted_from = "", $converted_to = "")
{
    $time = time();
    global $database;
    if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
        $database->insert("users_logs", array(
            "owner" => $owner,
            "type" => $type,
            "action" => $action,
            "log_id" => $log_id,
            "package_id" => $package,
            "cost" => $cost,
            "credits_after" => $credits_after,
            "date" => time(),
            "deleted_info" => json_encode(['username' => $username, 'password' => $password, 'member_id' => $owner, "converted_from" => $converted_from, "converted_to" => $converted_to]),
        ));
        return true;
    } else {
    }
    $PDO = getconnection();
    if ($PDO !== NULL) {
        $sql = "INSERT INTO `reg_userlog` (`id`, `owner`, `username`, `password`, `date`, `type`) VALUES (NULL, :owner, :username, :password, unix_timestamp(NOW()), :type);";
        $database = $PDO->prepare($sql);
        $database->bindParam(":owner", $owner, PDO::PARAM_INT);
        $database->bindParam(":username", $username, PDO::PARAM_STR);
        $database->bindParam(":password", $password, PDO::PARAM_STR);
        $database->bindParam(":type", $text, PDO::PARAM_STR, 255);
        if ($database->execute()) {
            return true;
        }
    }
    return false;
}
function insertCreditsLog($target_id, $admin_id, $amount, $reason)
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "INSERT INTO `users_credits_logs` (`id`, `target_id`, `admin_id`, `amount`, `date`, `reason`) VALUES (NULL, :target_id, :admin_id, :amount, unix_timestamp(NOW()), :reason);";
        } else {
            $sql = "INSERT INTO `credits_log` (`id`, `target_id`, `admin_id`, `amount`, `date`, `reason`) VALUES (NULL, :target_id, :admin_id, :amount, unix_timestamp(NOW()), :reason);";
        }
        $database = $PDO->prepare($sql);
        $database->bindParam(":target_id", $target_id, PDO::PARAM_INT);
        $database->bindParam(":admin_id", $admin_id, PDO::PARAM_INT);
        $database->bindParam(":amount", $amount, PDO::PARAM_INT);
        $database->bindParam(":reason", $reason, PDO::PARAM_STR);
        if ($database->execute()) {
            return true;
        }
    }
    return false;
}
function injectCustomCss()
{
    $custom_file_name = basename($_SERVER["SCRIPT_FILENAME"], ".php") . "_style.css";
    $full_path = "dist/css/custom/" . $custom_file_name;
    if (file_exists($full_path)) {
        echo "<link rel=\"stylesheet\" href=\"" . $full_path . "\">" . PHP_EOL;
    }
}
function getTranslatedDuration($package)
{
    $duration_in = "";
    switch ($package["trial_duration_in"]) {
        case "minutes":
            $duration_in = "minuto(s)";
            break;
        case "hours":
            $duration_in = "hora(s)";
            break;
        case "days":
            $duration_in = "dia(s)";
            break;
        case "months":
            $duration_in = "mes(es)";
            break;
        case "years":
            $duration_in = "ano(s)";
            break;
    }
    return $package["trial_duration"] . " " . $duration_in;
}
function getServerDNS($server_id = 0)
{
    if ($server_id == 0) {
        $custom_DNS = getServerProperty('custom_dns');
        if (strlen($custom_DNS) > 4) {
            return $custom_DNS;
        } else {
            $PDO = getconnection();

            if ($PDO !== NULL) {
                if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
                    $sql = 'SELECT * FROM `servers` WHERE `is_main` = 1 LIMIT 1;';
                } else {
                    $sql = 'SELECT * FROM `streaming_servers` WHERE `can_delete` = 0 LIMIT 1;';
                }
                $database = $PDO->prepare($sql);
                $database->execute();
                $result = $database->fetch(PDO::FETCH_ASSOC);

                if ($result) {
                    if (!empty($result['domain_name'])) {
                        return 'http://' . $result['domain_name'] . ':' . $result['http_broadcast_port'];
                    }

                    return 'http://' . $result['server_ip'] . ':' . $result['http_broadcast_port'];
                }
            }
        }
        return false;
    } else {
        $PDO = getconnection();
        if ($PDO !== NULL) {
            if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
                $sql = 'SELECT * FROM `servers` WHERE `id` = :serverid LIMIT 1;';
            } else {
                $sql = 'SELECT * FROM `streaming_servers` WHERE `id` = :serverid LIMIT 1;';
            }
            $database = $PDO->prepare($sql);
            $database->bindParam(":serverid", $server_id, PDO::PARAM_INT);
            $database->execute();
            $result = $database->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                if (!empty($result['domain_name'])) {
                    return 'http://' . $result['domain_name'] . ':' . $result['http_broadcast_port'];
                }

                return 'http://' . $result['server_ip'] . ':' . $result['http_broadcast_port'];
            }
        }
    }
}
function getBaseURL()
{
    return sprintf("%s://%s%s%s", 'http' . (isSecure() ? 's' : ''), $_SERVER["SERVER_NAME"], isset($_SERVER["SERVER_PORT"]) && $_SERVER["SERVER_PORT"] != "80" && $_SERVER["SERVER_PORT"] != "443" ? ":" . $_SERVER["SERVER_PORT"] : "", substr(dirname($_SERVER["REQUEST_URI"]), -1) == "/" ? dirname($_SERVER["REQUEST_URI"]) : dirname($_SERVER["REQUEST_URI"]) . "/");
}
function ShortenList($list)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, OFFICE_CONFIG['shorten_url'] . "/?url=" . urlencode($list) . "&format=text&creator_id=" . intval($_SESSION['__l0gg3d_us3r__']));
    curl_setopt($ch, CURLOPT_POST, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    $response = curl_exec($ch);
    curl_close($ch);
    if ($response === false) {
        return $list;
    }
    return $response;
}
function GetList($username, $password, $type = "")
{
    $server_dns = getserverdns();
    switch ($type) {
        case "ssiptv":
            $list_url = shortenlist(OFFICE_CONFIG['ssiptv_url'] . "/ssiptv/get/" . $username . "/" . $password . "/download_m3u/");
            break;
        case "mpegts":
            $list_url = (string) $server_dns . "/get.php?username=" . $username . "&password=" . $password . "&type=m3u_plus&output=ts";
            break;
        case "m3u8":
            $list_url = (string) $server_dns . "/get.php?username=" . $username . "&password=" . $password . "&type=m3u_plus&output=hls";
            break;
        default:
            $list_url = shortenlist((string) $server_dns . "/get.php?username=" . $username . "&password=" . $password . "&type=m3u_plus&output=hls");
            break;
    }

    return $list_url;
}

function cryptPassword($password, $salt = "", $rounds = 20000)
{
    $hash = crypt($password, sprintf("\$6\$rounds=%d\$%s\$", $rounds, $salt));
    return $hash;
}
function str_limit($value, $limit = 100, $end = "...")
{
    if (!is_string($value)) {
        return "";
    }
    if (mb_strwidth($value, "UTF-8") <= $limit) {
        return $value;
    }
    return rtrim(mb_strimwidth($value, 0, $limit, "", "UTF-8")) . $end;
}

function smtpmailer($para, $assunto, $corpo, $custom_smtp = false, $reseller_id = "")
{
    if (!file_exists(__DIR__ . "/phpmailer/src/PHPMailer.php")) {
        return false;
    }

    require_once __DIR__ . "/phpmailer/src/PHPMailer.php";

    $mail = new PHPMailer\PHPMailer\PHPMailer();

    if ($reseller_id != "") {
        if ($custom_smtp) {
            $email_settings = getUserPropertyDecode($reseller_id, 'email_settings');
        } else {
            $email_settings = getServerPropertyDecode("email_settings");
        }
    } else if (gettype($custom_smtp) == 'array' && $reseller_id == "") {
        $email_settings = [
            'use_smtp' => 1,
            'smtp_server' => $custom_smtp['host'],
            'smtp_port' => $custom_smtp['port'],
            'smtp_username' => $custom_smtp['username'],
            'smtp_password' => $custom_smtp['password'],
            'smtp_name' => $custom_smtp['profile']['name'],
            'smtp_email' => $custom_smtp['profile']['email'],
        ];
    } else if (gettype($custom_smtp) != 'array') {
        $email_settings = getServerPropertyDecode("email_settings");
    }

    if ($email_settings["use_smtp"] == 1) {
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = strtolower($email_settings["encryption_type"]);
        $mail->Host = $email_settings["smtp_server"];
        $mail->Port = intval($email_settings["smtp_port"]);
        $mail->Username = $email_settings["smtp_username"];
        $mail->Password = $email_settings["smtp_password"];
    } else {
        $mail->isMail();
    }

    $mail->setFrom($email_settings["sender_email"], $email_settings["sender_name"]);
    $mail->addAddress($para);
    $mail->CharSet = "UTF-8";
    $mail->isHTML(true);
    $mail->Subject = $assunto;
    $mail->Body = $corpo;

    if (debugEnabled()) {
        $mail->SMTPDebug = 2;
    }

    if (!$mail->send()) {
        return false;
    }

    return true;
}

function getCategorieByID($categorie_id)
{
    $PDO = getconnection();

    if ($PDO !== NULL) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = 'SELECT `category_name` FROM `streams_categories` WHERE `id` = :categorie_id LIMIT 1;';
        } else {
            $sql = 'SELECT `category_name` FROM `stream_categories` WHERE `id` = :categorie_id LIMIT 1;';
        }
        $database = $PDO->prepare($sql);
        $database->bindParam(':categorie_id', $categorie_id, PDO::PARAM_INT);

        if ($database->execute()) {
            return $database->fetch(PDO::FETCH_COLUMN);
        }
    }

    return false;
}

function debugEnabled()
{
    if (defined("OFFICE_DEBUG") && OFFICE_DEBUG) {
        return true;
    }
    return false;
}
function random_str($length, $keyspace = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ")
{
    $str = "";
    $max = mb_strlen($keyspace, "8bit") - 1;
    if ($max < 1) {
        return "";
    }
    for ($i = 0; $i < $length; $i++) {
        $str .= $keyspace[rand(0, $max)];
    }
    return $str;
}
function getRandomMD5($salt = "")
{
    return md5(random_str(10) . "#" . $salt);
}
function checkLicence($licence_key)
{
}
function writeLocalKey($localKey)
{
}
######### HOSTMK CODE ##########

function CodeGenerator(int $length = 8, string $type = "1")
{
    $lmin = 'abcdefghjkmnpqrstuvwxyz';
    $lmai = 'ABCDEFGHJKMNPQRSTUVWXYZ';
    $num = '123456789';
    $symb = '-';

    $characters = '';

    switch ($type) {
        case '1':
            $characters .= $num;
            break;
        case '1a':
            $characters .= $num;
            $characters .= $lmin;
            break;
        case '1aA':
            $characters .= $num;
            $characters .= $lmin;
            $characters .= $lmai;
            break;
        case '1aA-':
            $characters .= $num;
            $characters .= $lmin;
            $characters .= $lmai;
            $characters .= $symb;
            break;
    }
    $code = "";
    $len = strlen($characters);
    for ($n = 1; $n <= $length; $n++) {
        $rand = mt_rand(1, $len);
        $code .= $characters[$rand - 1];
    }
    return $code;
}

function TemplateReplace(int $reseller, string $username, string $password, string $duration, string $type = "iptv", int $chatbot_id = 0)
{
    $server_name = getServerProperty("server_name");
    $whatsapp = getUserProperty($reseller, 'whatsapp');
    $telegram = getUserProperty($reseller, 'telegram');

    if ($type == "binstream") {
        $template = getUserProperty($reseller, 'fast_test_template_p2p');
        if (empty($template)) {
            $template = getServerProperty('default_test_template_p2p');
        }

        if ($chatbot_id > 0) {
            $template = getChatbotRuleById($chatbot_id)['response'];
        }

        $template = str_replace('#username#', $username, $template);
        $template = str_replace('#password#', $password, $template);
        $template = str_replace('#duration#', $duration, $template);
    } elseif ($type == "exp_message") {
        $template = getUserProperty($reseller, 'expiring_template');
        if (empty($template)) {
            $template = getServerProperty('default_expiring_template');
        }

        $template = str_replace('#username#', $username, $template);
        $template = str_replace('#password#', $password, $template);
    } elseif ($type == "code") {
        $template = getUserProperty($reseller, 'fast_test_template_code');
        if (empty($template)) {
            $template = getServerProperty('default_test_template_code');
        }

        if ($chatbot_id > 0) {
            $template = getChatbotRuleById($chatbot_id)['response'];
        }

        $template = str_replace('#username#', $username, $template);
        $template = str_replace('#password#', $password, $template);
        $template = str_replace('#duration#', $duration, $template);
    } else {
        $template = getUserProperty($reseller, 'fast_test_template_iptv');
        if (empty($template)) {
            $template = getServerProperty('default_test_template_iptv');
        }

        if ($chatbot_id > 0) {
            $template = getChatbotRuleById($chatbot_id)['response'];
        }

        $m3u_link = GetList($username, $password);
        $m3u_link_hls = GetList($username, $password, "m3u8");
        $m3u_link_mpegts = GetList($username, $password, "mpegts");
        $ssiptv_link = GetList($username, $password, "ssiptv");

        $template = str_replace('#username#', $username, $template);
        $template = str_replace('#password#', $password, $template);
        $template = str_replace('#m3u_link#', $m3u_link, $template);
        $template = str_replace('#m3u_link_hls#', $m3u_link_hls, $template);
        $template = str_replace('#m3u_link_mpegts#', $m3u_link_mpegts, $template);
        $template = str_replace('#ssiptv_link#', $ssiptv_link, $template);
    }

    $reseller = getUserByID($reseller);
    if (is_numeric($duration)) {
        $date_now = time();
        if ((date("d", $date_now) + intval(1)) == date("d", $duration)) {
            $exp_info = "expira Amanhã";
        } elseif (date("d", $date_now) == date("d", $duration)) {
            $exp_info = "expira Hoje";
        } elseif ((date("d", $date_now) - intval(1)) == date("d", $duration)) {
            $exp_info = "expirou Ontem";
        } elseif (date("d", $date_now) > date("d", $duration)) {
            $exp_info = "expirou";
        } else {
            $exp_info = "expira em " . date("d/m/Y H:i", $duration);
        }
        $exp_date = date("d/m/Y H:i", $duration);
    } else {
        $exp_info = $duration;
        $exp_date = $duration;
    }

    $template = str_replace('#duration#', $exp_date, $template);
    $template = str_replace('#exp_date#', $exp_date, $template);
    $template = str_replace('#exp_info#', $exp_info, $template);
    $template = str_replace('#reseller_email#', is_null($reseller['email']) ? "" : $reseller['email'], $template);
    $template = str_replace('#server_name#', $server_name, $template);
    $template = str_replace('#whatsapp#', $whatsapp, $template);
    $template = str_replace('#telegram#', $telegram, $template);

    return $template;
}

function Verify_m3u($m3u_link)
{

    $data = parse_url($m3u_link);
    #TODO - Verificar se o link é valido
    @parse_str($data['query'], $result2);
    if (strstr($m3u_link, "username") && strstr($m3u_link, "password")) {
        $url_player_api = "http://" . $data['host'] . "/player_api.php?username=" . $result2['username'] . "&password=" . $result2['password'];
    } else {
        $data_path = explode("/", $data['path']);
        $url_player_api = "http://" . $data['host'] . "/player_api.php?username=" . $data_path[2] . "&password=" . $data_path[3];
    }
    $result['error'] = false;

    if (get_http_response_code($url_player_api) != "200") {
        $result['error'] = "invalid_url";
        return $result;
    } else {
        $options = array(
            'http' => array(
                'method' => "GET",
                'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.81 Safari/537.36\r\n"
            )
        );
        $context = stream_context_create($options);
        $result = json_decode(file_get_contents($url_player_api, false, $context), true);
        if (existClient($result['user_info']['username'])) {
            $result['error'] = "client_exist";
            return $result;
        }
        if ($result['user_info']['is_trial'] == 1) {
            $result['error'] = "is_trial";
            return $result;
        }

        $exp_date = $result['user_info']['exp_date'];
        $exp_date = date('d/m/Y', $exp_date);

        $today = implode('-', array_reverse(explode('/', date('d/m/Y'))));
        $data2 = implode('-', array_reverse(explode('/', $exp_date)));

        $d1 = strtotime($today);
        $d2 = strtotime($data2);

        $DaysToExpire = ($d2 - $d1) / 86400;
        if ($DaysToExpire < 0) {
            $DaysToExpire *= -1;
        }

        // if ($DaysToExpire > intval(29)) {
        //     $result['error'] = "many_days";
        //     return $result;
        // } else {
        $result['DaysToExpire'] = $DaysToExpire;
        if (getServerProperty('iptv_migration_fee', 1)) {
            $credits = number_format($DaysToExpire * 1 / 30, 2, '.', '');
            $result['credits'] = $credits * $result['user_info']['max_connections'];
        } else {
            $result['credits'] = "Migração Gratuita";
        }
        // }


        return $result;
    }
}

function get_http_response_code($url)
{
    $options = array(
        'http' => array(
            'method' => "GET",
            'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.81 Safari/537.36\r\n"
        )
    );
    $context = stream_context_create($options);

    $headers = get_headers($url, 0, $context);
    return substr($headers[0], 9, 3);
}

function migrate_cliente($url)
{
}

function get_logo($size = "big")
{
    if ($size == "big") {
        $logo = getServerProperty('server_logo_big', 0);
        if (strlen($logo) > 3) {
            return $logo;
        } else {
            return "/dist/img/logo_big.png";
        }
    } elseif ($size == "small") {
        $logo = getServerProperty('server_logo_small', 0);
        if (strlen($logo) > 3) {
            return $logo;
        } else {
            return "/dist/img/logo_small.png";
        }
    } else {
        return "/dist/img/logo_big.png";
    }
}

# Client Area Functions

function loginClient($username, $password)
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "SELECT * FROM `lines` WHERE `username` = :username AND `password` = :password LIMIT 1;";
        } else {
            $sql = "SELECT * FROM `users` WHERE `username` = :username AND `password` = :password LIMIT 1;";
        }
        $database = $PDO->prepare($sql);
        $database->bindParam(":username", $username, PDO::PARAM_STR, 50);
        $database->bindParam(":password", $password, PDO::PARAM_STR, 255);
        $database->execute();
        $result = $database->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            if (($result["admin_enabled"] == 1) and ($result["enabled"] == 1)) {
                startsession();
                $_SESSION["__l0gg3d_Client__"] = $result["id"];
                return 1;
            }
            return 4;
        }
        return 3;
    }
    return 2;
}

function isClientLogged($destination = "../index.php")
{
    startsession();
    if (!isset($_SESSION["__l0gg3d_Client__"])) {
        header("Location: " . $destination);
        exit;
    }
}

function getLoggedClient()
{
    startsession();
    if (isset($_SESSION["__l0gg3d_Client__"])) {
        $user = getClientByID($_SESSION["__l0gg3d_Client__"]);
        if ($user) {
            return $user;
        }
    }
    logoutuser();
    exit;
}

function logoutClient()
{
    startsession();
    unset($_SESSION);
    SESSION_DESTROY();
}

function getClientConnections($client_id)
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "SELECT count(*) FROM `lines_live` WHERE `user_id` = :client_id;";
        } else {
            $sql = "SELECT count(*) FROM `user_activity_now` WHERE `user_id` = :client_id;";
        }
        $database = $PDO->prepare($sql);
        $database->bindParam(":client_id", $client_id, PDO::PARAM_STR, 255);
        $database->execute();
        return $database->fetchColumn();
    }
    return 0;
}

function deleteClientPlan($user_id, $plan_id)
{
    $plans = json_decode(getUserProperty($user_id, "client_area_plans", "", true), true);
    // $plans = getUserPropertyDecode($user_id, "client_area_plans");
    $indexPlan = array_search($plan_id, array_column($plans, 'id'));

    if ($indexPlan !== false) {
        unset($plans[$indexPlan]);
    }

    $result = updateUserProperty($user_id, 'client_area_plans', json_encode($plans));

    return $result;
}

function getClientPlanByID($user_id, $plan_id)
{

    $plans = getUserPropertyDecode($user_id, "client_area_plans");
    $indexPlan = array_search($plan_id, array_column($plans, 'id'));

    if ($indexPlan !== false) {
        return $plans[$indexPlan];
    }

    return false;
}

function isSecure()
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || $_SERVER['SERVER_PORT'] == 443;
}

function getCurrentPath()
{
    return 'http' . (isSecure() ? 's' : '') . '://' . trim($_SERVER['HTTP_HOST'], "/") . $_SERVER['SCRIPT_NAME'];
}

/**
 * Create payment in database
 * 
 * @param $data [
 *  'status' => 'approved' | 'in_process' | 'rejected',
 *  'seller_id' => number,
 *  'buyer_id' => number,
 * ]
 * 
 * @return false || number
 */
function createPayment(array $data)
{
    if (empty($data['plan_type']) || empty($data['plan_id']) || empty($data['seller_id']) || empty($data['buyer_id']) || empty($data['amount'])) {
        return false;
    }

    if (empty($data['status'])) {
        $data['status'] = 'in_process';
    }

    if (empty($data['ip'])) {
        $data['ip'] = 'unknown';
    }

    $PDO = getofficeconnection();

    if ($PDO !== NULL) {
        $querySql = "INSERT INTO `payments` ( `status`, `plan_type`, `plan_id`, `seller_id`, `buyer_id`, `amount`, `ip`, `created_at`, `modified_at`) VALUES (:status, :plan_type, :plan_id, :seller_id, :buyer_id, :amount, :ip, unix_timestamp(NOW()), :modified_at);";

        $database = $PDO->prepare($querySql);
        $time_now = time();

        $database->bindParam(":status", $data['status'], PDO::PARAM_STR, 255);
        $database->bindParam(":plan_type", $data['plan_type'], PDO::PARAM_STR, 255);
        $database->bindParam(":plan_id", $data['plan_id'], PDO::PARAM_STR, 255);
        $database->bindParam(":seller_id", $data['seller_id'], PDO::PARAM_INT);
        $database->bindParam(":buyer_id", $data['buyer_id'], PDO::PARAM_INT);
        $database->bindParam(":amount", $data['amount'], PDO::PARAM_STR, 255);
        $database->bindParam(":ip", $data['ip'], PDO::PARAM_STR, 255);
        $database->bindParam(":modified_at", $time_now, PDO::PARAM_INT);

        if ($database->execute()) {
            return $PDO->lastInsertId();
        }
    }

    return false;
}

function updatePayment(int $id, array $data)
{
    if (empty($id)) {
        return false;
    }

    $PDO = getofficeconnection();

    if ($PDO !== NULL) {
        $data_status = isset($data['status']) && !empty($data['status']);
        $data_gateway_name = isset($data['gateway_name']) && !empty($data['gateway_name']);

        $querySql = "UPDATE `payments` SET ";

        if ($data_status) {
            $querySql .= "`status`= :status,";
        }

        if ($data_gateway_name) {
            $querySql .= "`gateway_name`= :gateway_name,";
        }

        $querySql .= "`modified_at` = :modified_at WHERE `id` = :payment_id;";

        $database = $PDO->prepare($querySql);
        $time_now = time();

        if ($data_status) {
            $database->bindParam(":status", $data['status'], PDO::PARAM_STR, 255);
        }

        if ($data_gateway_name) {
            $database->bindParam(":gateway_name", $data['gateway_name'], PDO::PARAM_STR, 255);
        }

        $database->bindParam(":modified_at", $time_now, PDO::PARAM_INT);
        $database->bindParam(":payment_id", $id, PDO::PARAM_INT, 255);
        if ($database->execute()) {
            return true;
        }
    }
    return false;
}

function getPaymentByID(int $id)
{
    if (empty($id)) {
        return false;
    }
    $PDO = getofficeconnection();
    if ($PDO !== NULL) {
        $querySql = "SELECT * FROM `payments` WHERE `id` = :payment_id;";
        $database = $PDO->prepare($querySql);
        $database->bindParam(":payment_id", $id, PDO::PARAM_INT, 255);
        if ($database->execute()) {
            return $database->fetch(PDO::FETCH_ASSOC);
        }
    }
    return false;
}

function getPaymentsBySellerID(int $seller_id)
{
    if (empty($seller_id)) {
        return false;
    }
    $PDO = getofficeconnection();
    if ($PDO !== NULL) {
        $querySql = "SELECT * FROM `payments` WHERE `seller_id` = :seller_id ORDER BY `id` DESC;";
        $database = $PDO->prepare($querySql);
        $database->bindParam(":seller_id", $seller_id, PDO::PARAM_INT, 255);
        if ($database->execute()) {
            return $database->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    return array();
}

function getPaymentsByBuyerID(int $buyer_id)
{
    if (empty($buyer_id)) {
        return false;
    }
    $PDO = getofficeconnection();
    if ($PDO !== NULL) {
        $querySql = "SELECT * FROM `payments` WHERE `buyer_id` = :buyer_id ORDER BY `id` DESC;";
        $database = $PDO->prepare($querySql);
        $database->bindParam(":buyer_id", $buyer_id, PDO::PARAM_INT, 255);
        if ($database->execute()) {
            return $database->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    return array();
}
function setActionPayment(string $status, int $payment_id)
{
    $paymentInternal = getPaymentByID(intval($payment_id));
    $safe_status = $status;

    switch ($status) {
        case 'pending':
            $safe_status = 'in_process';
            break;
        case 'success':
            $safe_status = 'approved';
            break;
        case 'failure':
            $safe_status = 'rejected';
            break;
    }

    if ($paymentInternal) {
        if ($safe_status == "approved") {
            $plan = getClientPlanByID($paymentInternal['seller_id'], $paymentInternal['plan_id']);

            renewClient($paymentInternal['buyer_id'], intval($plan['duration']));
            updatePayment($paymentInternal['id'], [
                'status' => 'approved'
            ]);
        } else if ($safe_status == "rejected") {
            updatePayment($paymentInternal['id'], [
                'status' => 'rejected'
            ]);
        }
    }
}

function getTransactions($userid, $start, $length, $search, $order_column_index, $order_type)
{
    $reseller = getuserbyid($userid);
    if ($reseller) {
        $columns = array(
            array("db" => "id"),
            array("db" => "seller_id", "formatter" => function ($d, $row) {
                $seller = getUserByID($row['seller_id']);
                return (!$seller) ? "Desconhecido" : $seller['username'];
            }),
            array("db" => "buyer_id", "formatter" => function ($d, $row) {
                $buyer = getClientByID($row['buyer_id']);
                return (!$buyer) ? "Desconhecido" : $buyer['username'];
            }),
            array("db" => "plan_type", "formatter" => function ($d, $row) {
                $plan_type = "";
                switch ($row["plan_type"]) {
                    case 'client_renew':
                        $plan_type = "<span class=\"badge badge-info\">Renovação</span>";
                        break;
                    case 'credits':
                        $plan_type = "<span class=\"badge badge-secondary\">Créditos</span>";
                        break;
                }
                return $plan_type;
            }),
            array("db" => "plan_id", "formatter" => function ($d, $row) {
                $plan = "";
                if ($row['plan_type'] == "client_renew") {
                    $plan = getClientPlanByID($row['seller_id'], $row['plan_id']);
                }
                return $plan['name'];
            }),
            array("db" => "amount", "formatter" => function ($d, $row) {
                return "R$ " . number_format($row['amount'], 2, ',', '.');
            }),
            array("db" => "gateway_name", "formatter" => function ($d, $row) {
                $gateway_name = "";
                switch ($row["gateway_name"]) {
                    case 'mercadopago':
                        $gateway_name = "MercadoPago";
                        break;
                    case 'pagseguro':
                        $gateway_name = "PagSeguro";
                        break;
                }
                return $gateway_name;
            }),
            array("db" => "status", "formatter" => function ($d, $row) {
                $status = "";
                switch ($row["status"]) {
                    case 'approved':
                        $status = "<span class=\"badge badge-success\">Aprovado</span>";
                        break;
                    case 'in_process':
                        $status = "<span class=\"badge badge-warning\">Em Processamento</span>";
                        break;
                    case 'rejected':
                        $status = "<span class=\"badge badge-danger\">Negado</span>";
                        break;
                }
                return $status;
            }),
            array("db" => "modified_at", "formatter" => function ($d, $row) {

                return date('d/m/Y H:i', $row["modified_at"]);;
            })

        );


        $transactions = isAdmin($reseller) ? getAllTransactionsAdminWithOptions($start, $length, $columns, $search, $order_column_index, $order_type) : getAllTransactionsByOwnerWithOptions($reseller, $start, $length, $columns, $search, $order_column_index, $order_type);
        return $transactions;
    }
    return array();
}

function getAllTransactionsAdminWithOptions($start = 0, $length = 10, $columns = array(), $search_value = "", $order_column_index = NULL, $order_type = "asc")
{
    $result = array("data" => array(), "recordsTotal" => 0, "recordsFiltered" => 0);
    $all_resellers = dataoutput($columns, getallTransactions());
    if ($order_column_index !== NULL && isset($columns[$order_column_index]["db"])) {
        $order_column = $columns[$order_column_index]["db"];
        usort($all_resellers, function ($a, $b) use ($order_column, $order_type) {
            if ($a[$order_column] === $b[$order_column]) {
                return 0;
            }
            if ($order_type == "asc") {
                return strip_tags($b[$order_column]) < strip_tags($a[$order_column]) ? 1 : -1;
            }
            return strip_tags($a[$order_column]) < strip_tags($b[$order_column]) ? 1 : -1;
        });
    }
    $current_index = 0;
    foreach ($all_resellers as $current_reseller) {
        if (tryfind($current_reseller, $columns, $search_value)) {
            if ($start <= $current_index && count($result["data"]) < $length) {
                $result["data"][] = $current_reseller;
            }
            $current_index++;
        }
    }
    $result["recordsTotal"] = count($all_resellers);
    $result["recordsFiltered"] = $current_index;
    return $result;
}
function getallTransactions()
{
    $PDO = getofficeconnection();
    if ($PDO !== NULL) {
        $sql = "SELECT * FROM `payments`";
        $database = $PDO->prepare($sql);
        if ($database->execute()) {
            return $database->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    return array();
}
function getAllTransactionsByOwnerWithOptions($reseller, $start = 0, $length = 10, $columns = array(), $search_value = "", $order_column_index = NULL, $order_type = "asc")
{
    $result = array("data" => array(), "recordsTotal" => 0, "recordsFiltered" => 0);
    $all_resellers = dataoutput($columns, getallTransactionsbyreseller($reseller["id"]));
    if ($order_column_index !== NULL && isset($columns[$order_column_index]["db"])) {
        $order_column = $columns[$order_column_index]["db"];
        usort($all_resellers, function ($a, $b) use ($order_column, $order_type) {
            if ($a[$order_column] === $b[$order_column]) {
                return 0;
            }
            if ($order_type == "asc") {
                return strip_tags($b[$order_column]) < strip_tags($a[$order_column]) ? 1 : -1;
            }
            return strip_tags($a[$order_column]) < strip_tags($b[$order_column]) ? 1 : -1;
        });
    }
    $current_index = 0;
    foreach ($all_resellers as $current_reseller) {
        if (tryfind($current_reseller, $columns, $search_value)) {
            if ($start <= $current_index && count($result["data"]) < $length) {
                $result["data"][] = $current_reseller;
            }
            $current_index++;
        }
    }
    $result["recordsTotal"] = count($all_resellers);
    $result["recordsFiltered"] = $current_index;
    return $result;
}
function getallTransactionsbyreseller($reseller_id)
{
    $PDO = getofficeconnection();
    if ($PDO !== NULL) {
        $sql = "SELECT * FROM `payments` WHERE `seller_id` = :reseller_id";
        $database = $PDO->prepare($sql);
        $database->bindParam(":reseller_id", $reseller_id, PDO::PARAM_INT);
        if ($database->execute()) {
            return $database->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    return array();
}

function getClientTransactions($userid, $start, $length, $search, $order_column_index, $order_type)
{
    $client = getClientByID($userid);
    if ($client) {
        $columns = array(
            array("db" => "id"),
            array("db" => "seller_id", "formatter" => function ($d, $row) {
                $seller = getUserByID($row['seller_id']);
                return (!$seller) ? "Desconhecido" : $seller['username'];
            }),
            array("db" => "buyer_id", "formatter" => function ($d, $row) {
                $buyer = getClientByID($row['buyer_id']);
                return (!$buyer) ? "Desconhecido" : $buyer['username'];
            }),
            array("db" => "plan_type", "formatter" => function ($d, $row) {
                $plan_type = "";
                switch ($row["plan_type"]) {
                    case 'client_renew':
                        $plan_type = "<span class=\"badge badge-info\">Renovação</span>";
                        break;
                    case 'credits':
                        $plan_type = "<span class=\"badge badge-secondary\">Créditos</span>";
                        break;
                }
                return $plan_type;
            }),
            array("db" => "plan_id", "formatter" => function ($d, $row) {
                $plan = "";
                if ($row['plan_type'] == "client_renew") {
                    $plan = getClientPlanByID($row['seller_id'], $row['plan_id']);
                }
                return $plan['name'];
            }),
            array("db" => "amount", "formatter" => function ($d, $row) {
                return "R$ " . number_format($row['amount'], 2, ',', '.');
            }),
            array("db" => "gateway_name", "formatter" => function ($d, $row) {
                $gateway_name = "";
                switch ($row["gateway_name"]) {
                    case 'mercadopago':
                        $gateway_name = "MercadoPago";
                        break;
                    case 'pagseguro':
                        $gateway_name = "PagSeguro";
                        break;
                }
                return $gateway_name;
            }),
            array("db" => "status", "formatter" => function ($d, $row) {
                $status = "";
                switch ($row["status"]) {
                    case 'approved':
                        $status = "<span class=\"badge badge-success\">Aprovado</span>";
                        break;
                    case 'in_process':
                        $status = "<span class=\"badge badge-warning\">Em Processamento</span>";
                        break;
                    case 'rejected':
                        $status = "<span class=\"badge badge-danger\">Negado</span>";
                        break;
                }
                return $status;
            }),
            array("db" => "modified_at", "formatter" => function ($d, $row) {

                return date('d/m/Y H:i', $row["modified_at"]);;
            })
        );
        $transactions = getAllTransactionsByClientWithOptions($client, $start, $length, $columns, $search, $order_column_index, $order_type);
        return $transactions;
    }
    return array();
}

function getAllTransactionsByClientWithOptions($client, $start = 0, $length = 10, $columns = array(), $search_value = "", $order_column_index = NULL, $order_type = "asc")
{
    $result = array("data" => array(), "recordsTotal" => 0, "recordsFiltered" => 0);
    $all_resellers = dataoutput($columns, getallTransactionsbyClient($client["id"]));
    if ($order_column_index !== NULL && isset($columns[$order_column_index]["db"])) {
        $order_column = $columns[$order_column_index]["db"];
        usort($all_resellers, function ($a, $b) use ($order_column, $order_type) {
            if ($a[$order_column] === $b[$order_column]) {
                return 0;
            }
            if ($order_type == "asc") {
                return strip_tags($b[$order_column]) < strip_tags($a[$order_column]) ? 1 : -1;
            }
            return strip_tags($a[$order_column]) < strip_tags($b[$order_column]) ? 1 : -1;
        });
    }
    $current_index = 0;
    foreach ($all_resellers as $current_reseller) {
        if (tryfind($current_reseller, $columns, $search_value)) {
            if ($start <= $current_index && count($result["data"]) < $length) {
                $result["data"][] = $current_reseller;
            }
            $current_index++;
        }
    }
    $result["recordsTotal"] = count($all_resellers);
    $result["recordsFiltered"] = $current_index;
    return $result;
}

function getallTransactionsbyClient($client_id)
{
    $PDO = getOfficeConnection();
    if ($PDO !== NULL) {
        $sql = "SELECT * FROM `payments` WHERE `buyer_id` = :client_id";
        $database = $PDO->prepare($sql);
        $database->bindParam(":client_id", $client_id, PDO::PARAM_INT);
        if ($database->execute()) {
            return $database->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    return array();
}

function totalScreenAndClientsInTree(int $reseller_id)
{
    $PDO = getConnection();
    $date = time();
    if ($PDO !== NULL) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sqlQuery = "WITH RECURSIVE resellers AS ( 
                SELECT id, owner_id FROM users WHERE id = :reseller_id
                UNION ALL 
                SELECT o.id, o.owner_id FROM users o JOIN resellers t ON t.id = o.owner_id
            ) SELECT count(*) as total_users, SUM(max_connections) as total_max_connections
            FROM resellers as resellers
            JOIN `lines`
            on lines.member_id = resellers.id
            WHERE lines.enabled = 1
                AND lines.admin_enabled = 1
                AND lines.is_trial = 0
                AND (
                 lines.exp_date > :timestamp_now OR
                 lines.exp_date is null
                );";
        } elseif (IsMariaDB()) {
            $sqlQuery = "WITH RECURSIVE resellers AS ( 
            SELECT id, 
                owner_id
            FROM reg_users
            WHERE id = :reseller_id

            UNION ALL 

            SELECT o.id,
                o.owner_id
            FROM reg_users o
            JOIN resellers t ON t.id = o.owner_id
        ) SELECT count(*) as total_users, SUM(max_connections) as total_max_connections
        FROM resellers as resellers
        JOIN users
        on users.member_id = resellers.id
        WHERE users.enabled = 1
            AND users.admin_enabled = 1
            AND users.is_trial = 0
            AND (
              users.exp_date > :timestamp_now OR
              users.exp_date is null
            );";
        } else {
            $sqlQuery = "SELECT COUNT(*) AS total_users, SUM(u.max_connections) AS total_max_connections
            FROM (
              SELECT id, owner_id
              FROM reg_users
              WHERE id = :reseller_id
            
              UNION ALL
            
              SELECT o.id, o.owner_id
              FROM reg_users o
              JOIN (
                SELECT id, owner_id
                FROM reg_users
                WHERE id = :reseller_id
              ) t ON t.id = o.owner_id
            ) AS resellers
            JOIN users u ON u.member_id = resellers.id
            WHERE u.enabled = 1
              AND u.admin_enabled = 1
              AND u.is_trial = 0
              AND (u.exp_date > :timestamp_now OR u.exp_date IS NULL);";
        }

        $database = $PDO->prepare($sqlQuery);
        $database->bindParam(":reseller_id", $reseller_id, PDO::PARAM_INT);
        $database->bindParam(":timestamp_now", $date, PDO::PARAM_INT);

        if ($database->execute()) {
            return $database->fetch(PDO::FETCH_ASSOC);
        }
    }
    return null;
}

function updateTables()
{
    global $database;
    $table = OFFICE_CONFIG['remote_db']['panel_type'] == "XUI" ? "lines" : "users";
    $columnsDB = $database->rawQuery("SHOW COLUMNS FROM `$table`;");
    $columns = array();
    foreach ($columnsDB as $column) {
        array_push($columns, $column['Field']);
    }

    $requiredColumns = ['email', 'phone', 'trust_renew'];
    $result = true;

    foreach ($requiredColumns as $column) {
        $exists = !empty(array_search($column, $columns)) ? true : false;
        if (!$exists) {
            $queryResult = $database->rawQuery("ALTER TABLE `$table` ADD `$column` VARCHAR(255) NULL DEFAULT NULL AFTER `password`;");
            if (!$queryResult) {
                $result = false;
            }
        }
    }
    return $result;
}

function updateOfficeTables()
{
    global $databaseOffice;

    $chatbot = $databaseOffice->tableExists('chatbot');
    if (!$chatbot) {
        $databaseOffice->rawQuery("CREATE TABLE
        `chatbot` (
          `id` int(11) PRIMARY KEY AUTO_INCREMENT,
          `status` INT NOT NULL DEFAULT 1,
          `rule_type` varchar(255) DEFAULT NULL,
          `rule_action` varchar(255) DEFAULT NULL,
          `response` text,
          `reseller` int(11) DEFAULT NULL,
          `runs` int(11) DEFAULT 0,
          `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
    }

    $chatbot_messages = $databaseOffice->tableExists('chatbot_messages');
    if (!$chatbot_messages) {
        $databaseOffice->rawQuery("CREATE TABLE
        `chatbot_messages` (
          `id` int(11) PRIMARY KEY AUTO_INCREMENT,
          `chatbot_id` int(11) DEFAULT NULL,
          `reseller` int(11) DEFAULT NULL,
          `message` text,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          FOREIGN KEY (chatbot_id) REFERENCES chatbot(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
    }

    $table = "chatbot";
    $columnsDB = $databaseOffice->rawQuery("SHOW COLUMNS FROM `$table`;");
    $columns = array();
    foreach ($columnsDB as $column) {
        array_push($columns, $column['Field']);
    }

    $requiredColumns = ['runs'];
    $result = true;

    foreach ($requiredColumns as $column) {
        $exists = !empty(array_search($column, $columns)) ? true : false;
        if (!$exists) {
            $queryResult = $databaseOffice->rawQuery("ALTER TABLE `$table` ADD `$column` INT(11) NOT NULL DEFAULT '0' AFTER `reseller`;");
            if (!$queryResult) {
                $result = false;
            }
        }
    }

    $table = "test_historic";
    $columnsDB = $databaseOffice->rawQuery("SHOW COLUMNS FROM `$table`;");
    $columns = array();
    foreach ($columnsDB as $column) {
        array_push($columns, $column['Field']);
    }

    $requiredColumns = ['type'];
    $result = true;

    foreach ($requiredColumns as $column) {
        $exists = !empty(array_search($column, $columns)) ? true : false;
        if (!$exists) {
            $queryResult = $databaseOffice->rawQuery("ALTER TABLE `$table` ADD `$column` VARCHAR(255) NULL DEFAULT NULL;");
            if (!$queryResult) {
                $result = false;
            }
        }
    }

    return $result;
}

function getCreditsLog($userid, $start, $length, $search_value, $order_column_index, $order_type)
{
    $reseller = getuserbyid($userid);
    if ($reseller) {
        $columns = array(
            array("db" => "id"),
            array("db" => "owner_username", "formatter" => function ($d, $row) {
                return (!$row['owner_username']) ? "-" : $row['owner_username'];
            }),
            array("db" => "target_username", "formatter" => function ($d, $row) {
                return (!$row['target_username']) ? "-" : $row['target_username'];
            }),
            array("db" => "amount", "formatter" => function ($d, $row) {
                return ($row['amount'] > 0) ? "<span class=\"badge badge-success\">" . $row['amount'] . "</span>" : "<span class=\"badge badge-danger\">" . $row['amount'] . "</span>";
            }),
            array("db" => "reason", "formatter" => function ($d, $row) {
                return (!$row['reason']) ? "" : $row['reason'];
            }),

            array("db" => "date", "formatter" => function ($d, $row) {

                return date('d/m/Y H:i', $row["date"]);;
            })
        );
        return isAdmin($reseller) ? getAllCreditsLogWithOptions($start, $length, $columns, $search_value, $order_column_index, $order_type) : getAllCreditsLogByOwnerWithOptions($reseller, $start, $length, $columns, $search_value, $order_column_index, $order_type);
    }
    return array();
}

function getAllCreditsLog()
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "SELECT `users_credits_logs`.`id`, `users_credits_logs`.`target_id`, `users_credits_logs`.`admin_id`, `target`.`username` AS `target_username`, `owner`.`username` AS `owner_username`, `amount`, `users_credits_logs`.`date`, `users_credits_logs`.`reason` FROM `users_credits_logs` LEFT JOIN `users` AS `target` ON `target`.`id` = `users_credits_logs`.`target_id` LEFT JOIN `users` AS `owner` ON `owner`.`id` = `users_credits_logs`.`admin_id`;";
        } else {
            $sql = "SELECT `credits_log`.`id`, `credits_log`.`target_id`, `credits_log`.`admin_id`, `target`.`username` AS `target_username`, `owner`.`username` AS `owner_username`, `amount`, `credits_log`.`date`, `credits_log`.`reason` FROM `credits_log` LEFT JOIN `reg_users` AS `target` ON `target`.`id` = `credits_log`.`target_id` LEFT JOIN `reg_users` AS `owner` ON `owner`.`id` = `credits_log`.`admin_id`;";
        }
        $database = $PDO->prepare($sql);
        if ($database->execute()) {
            return $database->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    return array();
}
function getAllCreditsLogByOwnerID($reseller_id)
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "SELECT `users_credits_logs`.`id`, `users_credits_logs`.`target_id`, `users_credits_logs`.`admin_id`, `target`.`username` AS `target_username`, `owner`.`username` AS `owner_username`, `amount`, `users_credits_logs`.`date`, `users_credits_logs`.`reason` FROM `users_credits_logs` LEFT JOIN `users` AS `target` ON `target`.`id` = `users_credits_logs`.`target_id` LEFT JOIN `users` AS `owner` ON `owner`.`id` = `users_credits_logs`.`admin_id` WHERE `users_credits_logs`.`target_id` = :reseller_id OR `users_credits_logs`.`admin_id` = :reseller_id;";
        } else {
            $sql = "SELECT `credits_log`.`id`, `credits_log`.`target_id`, `credits_log`.`admin_id`, `target`.`username` AS `target_username`, `owner`.`username` AS `owner_username`, `amount`, `credits_log`.`date`, `credits_log`.`reason` FROM `credits_log` LEFT JOIN `reg_users` AS `target` ON `target`.`id` = `credits_log`.`target_id` LEFT JOIN `reg_users` AS `owner` ON `owner`.`id` = `credits_log`.`admin_id` WHERE `credits_log`.`target_id` = :reseller_id OR `credits_log`.`admin_id` = :reseller_id;";
        }
        $database = $PDO->prepare($sql);
        $database->bindParam(":reseller_id", $reseller_id, PDO::PARAM_INT);
        if ($database->execute()) {
            return $database->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    return array();
}
function getAllCreditsLogWithOptions($start = 0, $length = 10, $columns = array(), $search_value = "", $order_column_index = NULL, $order_type = "asc")
{
    $result = array("data" => array(), "recordsTotal" => 0, "recordsFiltered" => 0);
    $all_resellers = dataoutput($columns, getAllCreditsLog());
    if ($order_column_index !== NULL && isset($columns[$order_column_index]["db"])) {
        $order_column = $columns[$order_column_index]["db"];
        usort($all_resellers, function ($a, $b) use ($order_column, $order_type) {
            if ($a[$order_column] === $b[$order_column]) {
                return 0;
            }
            if ($order_type == "asc") {
                return strip_tags($b[$order_column]) < strip_tags($a[$order_column]) ? 1 : -1;
            }
            return strip_tags($a[$order_column]) < strip_tags($b[$order_column]) ? 1 : -1;
        });
    }
    $current_index = 0;
    foreach ($all_resellers as $current_reseller) {
        if (tryfind($current_reseller, $columns, $search_value)) {
            if ($start <= $current_index && count($result["data"]) < $length) {
                $result["data"][] = $current_reseller;
            }
            $current_index++;
        }
    }
    $result["recordsTotal"] = count($all_resellers);
    $result["recordsFiltered"] = $current_index;
    return $result;
}
function getAllCreditsLogByOwnerWithOptions($reseller, $start = 0, $length = 10, $columns = array(), $search_value = "", $order_column_index = NULL, $order_type = "asc")
{
    $result = array("data" => array(), "recordsTotal" => 0, "recordsFiltered" => 0);
    $all_resellers = dataoutput($columns, getAllCreditsLogByOwnerID($reseller["id"]));
    if ($order_column_index !== NULL && isset($columns[$order_column_index]["db"])) {
        $order_column = $columns[$order_column_index]["db"];
        usort($all_resellers, function ($a, $b) use ($order_column, $order_type) {
            if ($a[$order_column] === $b[$order_column]) {
                return 0;
            }
            if ($order_type == "asc") {
                return strip_tags($b[$order_column]) < strip_tags($a[$order_column]) ? 1 : -1;
            }
            return strip_tags($a[$order_column]) < strip_tags($b[$order_column]) ? 1 : -1;
        });
    }
    $current_index = 0;
    foreach ($all_resellers as $current_reseller) {
        if (tryfind($current_reseller, $columns, $search_value)) {
            if ($start <= $current_index && count($result["data"]) < $length) {
                $result["data"][] = $current_reseller;
            }
            $current_index++;
        }
    }
    $result["recordsTotal"] = count($all_resellers);
    $result["recordsFiltered"] = $current_index;
    return $result;
}

function getResellerLog($userid, $start, $length, $search_value, $order_column_index, $order_type)
{
    $reseller = getuserbyid($userid);
    if ($reseller) {
        $columns = array(
            array("db" => "id"),
            array("db" => "owner", "formatter" => function ($d, $row) {
                return (!$row['owner']) ? "-" : $row['owner'];
            }),
            array("db" => "username", "formatter" => function ($d, $row) {
                return (!$row['username']) ? "-" : $row['username'];
            }),
            array("db" => "type", "formatter" => function ($d, $row) {
                return (!$row['type']) ? "" : $row['type'];
            }),
            array("db" => "date", "formatter" => function ($d, $row) {

                return date('d/m/Y H:i', $row["date"]);;
            })
        );
        return isAdmin($reseller) ? getAllResellerLogWithOptions($start, $length, $columns, $search_value, $order_column_index, $order_type) : getAllResellerLogByOwnerWithOptions($reseller, $start, $length, $columns, $search_value, $order_column_index, $order_type);
    }
    return array();
}

function getAllResellerLog()
{
    $PDO = getconnection();
    if ($PDO !== NULL) {
        if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
            $sql = "SELECT `reg_userlog`.`id`, `reg_userlog`.`owner` as `owner_id`, `reg_users`.`username` AS `owner`, `reg_userlog`.`username`, `reg_userlog`.`type`, `reg_userlog`.`date` AS `date` FROM `reg_userlog` LEFT JOIN `reg_users` ON `reg_users`.`id` = `reg_userlog`.`owner`;";
        } else {
            $sql = "SELECT `reg_userlog`.`id`, `reg_userlog`.`owner` as `owner_id`, `reg_users`.`username` AS `owner`, `reg_userlog`.`username`, `reg_userlog`.`type`, `reg_userlog`.`date` AS `date` FROM `reg_userlog` LEFT JOIN `reg_users` ON `reg_users`.`id` = `reg_userlog`.`owner`;";
        }
        $database = $PDO->prepare($sql);
        if ($database->execute()) {
            return $database->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    return array();
}
function getAllResellerLogByOwnerID($reseller_id)
{
    $PDO = getconnection();
    if ($PDO !== NULL) {

        $sql = "SELECT `reg_userlog`.`id`, `reg_userlog`.`owner` as `owner_id`, `reg_users`.`username` AS `owner`, `reg_userlog`.`username`, `reg_userlog`.`type`, `reg_userlog`.`date` AS `date` FROM `reg_userlog` LEFT JOIN `reg_users` ON `reg_users`.`id` = `reg_userlog`.`owner` WHERE `reg_userlog`.`owner` = :reseller_id";
        $database = $PDO->prepare($sql);
        $database->bindParam(":reseller_id", $reseller_id, PDO::PARAM_INT);
        if ($database->execute()) {
            return $database->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    return array();
}
function getAllResellerLogWithOptions($start = 0, $length = 10, $columns = array(), $search_value = "", $order_column_index = NULL, $order_type = "asc")
{
    $result = array("data" => array(), "recordsTotal" => 0, "recordsFiltered" => 0);
    $all_resellers = dataoutput($columns, getAllResellerLog());
    if ($order_column_index !== NULL && isset($columns[$order_column_index]["db"])) {
        $order_column = $columns[$order_column_index]["db"];
        usort($all_resellers, function ($a, $b) use ($order_column, $order_type) {
            if ($a[$order_column] === $b[$order_column]) {
                return 0;
            }
            if ($order_type == "asc") {
                return strip_tags($b[$order_column]) < strip_tags($a[$order_column]) ? 1 : -1;
            }
            return strip_tags($a[$order_column]) < strip_tags($b[$order_column]) ? 1 : -1;
        });
    }
    $current_index = 0;
    foreach ($all_resellers as $current_reseller) {
        if (tryfind($current_reseller, $columns, $search_value)) {
            if ($start <= $current_index && count($result["data"]) < $length) {
                $result["data"][] = $current_reseller;
            }
            $current_index++;
        }
    }
    $result["recordsTotal"] = count($all_resellers);
    $result["recordsFiltered"] = $current_index;
    return $result;
}
function getAllResellerLogByOwnerWithOptions($reseller, $start = 0, $length = 10, $columns = array(), $search_value = "", $order_column_index = NULL, $order_type = "asc")
{
    $result = array("data" => array(), "recordsTotal" => 0, "recordsFiltered" => 0);
    $all_resellers = dataoutput($columns, getAllResellerLogByOwnerID($reseller["id"]));
    if ($order_column_index !== NULL && isset($columns[$order_column_index]["db"])) {
        $order_column = $columns[$order_column_index]["db"];
        usort($all_resellers, function ($a, $b) use ($order_column, $order_type) {
            if ($a[$order_column] === $b[$order_column]) {
                return 0;
            }
            if ($order_type == "asc") {
                return strip_tags($b[$order_column]) < strip_tags($a[$order_column]) ? 1 : -1;
            }
            return strip_tags($a[$order_column]) < strip_tags($b[$order_column]) ? 1 : -1;
        });
    }
    $current_index = 0;
    foreach ($all_resellers as $current_reseller) {
        if (tryfind($current_reseller, $columns, $search_value)) {
            if ($start <= $current_index && count($result["data"]) < $length) {
                $result["data"][] = $current_reseller;
            }
            $current_index++;
        }
    }
    $result["recordsTotal"] = count($all_resellers);
    $result["recordsFiltered"] = $current_index;
    return $result;
}

function DarkMode($isClient = false)
{
    $officeDark = getServerProperty("dark_mode");
    if ($isClient) {
        return $officeDark;
    }
    $userid = getLoggedUser();
    $userDark = getUserProperty($userid["id"], "dark_mode", Null);

    if ($userDark) {
        return true;
    } elseif (($officeDark)) {
        if (!$userDark) {
            if ($userDark == Null) {
                return true;
            }
            return false;
        }
        return true;
    } else {
        return false;
    }
}

function toggleDarkMode()
{
    $userid = getLoggedUser();
    clearUserCache($userid["id"], "dark_mode");
    if (getUserProperty($userid['id'], "dark_mode", "", true)) {
        deleteUserProperty($userid['id'], "dark_mode");
        $value = 0;
    } else {
        deleteUserProperty($userid['id'], "dark_mode");
        $value = 1;
    }
    $result = addUserProperty($userid["id"], "dark_mode", $value);
    if ($result) {
        return true;
    }
    return false;
}


function importSQL()
{
    try {
        $sqlScript = file_get_contents(__DIR__ . '/db.sql');
        $PDO = getOfficeConnection();
        $PDO->exec($sqlScript);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

function checkUpdate($byPassCache = false)
{
    global $redis;
    $key = OFFICE_CONFIG['panel_id'] . "_server_property_have_update";
    $cached_value = $redis->get($key);
    if ($cached_value !== false && !$byPassCache) {
        return $cached_value;
    }
    try {
        include_once __DIR__ . "/class/CurlRequest.php";
        $request_url = "https://api.office.hostmk.com.br/api";
        $CurlRequest = new CurlRequest($request_url, "GET");
        $response = $CurlRequest->makeRequest();
        if ($response['http_code'] != 200) {
            return false;
        }

        $data = json_decode($response['response'], true);
        $result = version_compare(OFFICE_VERSION, $data["latest"]) < 0;
        $redis->setex($key, 43200, $result);

        return $result;
    } catch (\Exception $e) {
        return false;
    }
}

function requestUpdate($data)
{
    include_once __DIR__ . "/class/CurlRequest.php";
    $request_url = "https://n8n.hostmk.com.br/webhook/99e07c1c-4bc7-4687-812c-944e1f1cde5f";
    $CurlRequest = new CurlRequest($request_url, "POST", $data);
    $response = $CurlRequest->makeRequest();

    if ($response['http_code'] == 200) {
        clearServerCache("have_update");
        return true;
    }
    return false;
}

function hasUpdated($check = false)
{
    if (file_exists("./.update")) {
        updateTables();
        updateOfficeTables();
        if ($check) {
            unlink("./.update");
            if (!file_exists("./.update")) {
                return true;
            }
        } else {
            return true;
        }
    }
    return false;
}

function closeExpiration()
{
    $expiration_timestamp = getServerProperty("panel_expiration");
    if (is_null($expiration_timestamp) || empty($expiration_timestamp)) {
        return false;
    }

    // Convertendo o timestamp de milissegundos para segundos
    $expiration_timestamp = $expiration_timestamp / 1000;

    $expiration_date = date("d/m/Y", $expiration_timestamp);
    if ($expiration_date === false) {
        throw new Exception("Erro na conversão do timestamp para data.");
    }

    $now = time();
    $expiration_date_object = DateTime::createFromFormat("d/m/Y", $expiration_date);
    $now_object = DateTime::createFromFormat("d/m/Y", date("d/m/Y", $now));

    $diff = $now_object->diff($expiration_date_object);
    $days_diff = $diff->format("%r%a");



    if ($days_diff > 7) {
        return false;
    }

    $expiration = [];

    if ($days_diff <= 7) {
        if ($days_diff > 1) {
            $expiration['text'] = '<h5><i class="icon far fa-info"></i>Seu painel expira em ' . $diff->format("%a dias") . '</h5><p class="mb-0">Mantenha suas faturas em dia para evitar interrupção do seu serviço!</p>';
            $expiration['class'] = "alert alert-info alert-dismissible";
        } elseif ($days_diff == 1) {
            $expiration['text'] = '<h5><i class="icon far fa-exclamation-triangle"></i>Seu painel expira amanhã</h5><p class="mb-0">Mantenha suas faturas em dia para evitar interrupção do seu serviço!</p>';
            $expiration['class'] = "alert alert-warning alert-dismissible";
        } elseif ($days_diff == 0) {
            $expiration['text'] = '<h5><i class="icon far fa-exclamation-triangle"></i>Seu painel expira hoje</h5><p class="mb-0">Mantenha suas faturas em dia para evitar interrupção do seu serviço!</p>';
            $expiration['class'] = "alert alert-danger alert-dismissible";
        } elseif ($days_diff == -1) {
            $expiration['text'] = '<h5><i class="icon far fa-exclamation-triangle"></i>Seu painel expirou ontem</h5><p class="mb-0">Mantenha suas faturas em dia para evitar interrupção do seu serviço!</p>';
            $expiration['class'] = "alert alert-danger alert-dismissible";
        } elseif ($days_diff < -1) {
            $expiration['text'] = '<h5><i class="icon far fa-exclamation-triangle"></i>Seu painel expirou</h5><p class="mb-0">Mantenha suas faturas em dia para evitar interrupção do seu serviço!</p>';
            $expiration['class'] = "alert alert-danger alert-dismissible";
        } else {
            $expiration['text'] = '<h5><i class="icon far fa-exclamation-triangle"></i>Seu painel expira em ' . $expiration_date . '</h5><p class="mb-0">Mantenha suas faturas em dia para evitar interrupção do seu serviço!</p>';
            $expiration['class'] = "alert alert-info alert-dismissible";
        }
    }

    return $expiration;
}

function getAllowedPages($userid)
{
    $pages = json_decode(getServerProperty("partner_allowed_pages", '["geral", "informations", "fast_test_template", "email_config", "email_template", "clients", "ssiptv", "fast_test_sidebar", "fast_test_dash", "fast_test_dash"]', true), true);
    return $pages;
}

function getAllBinstreamClientsTable($userid, $start, $length, $search, $order_column_index, $order_type, $p2p = false)
{
    $reseller = getuserbyid($userid);
    if ($reseller) {
        $columns = array(
            array("db" => "id"), array("db" => "display_username", "formatter" => function ($d, $row) {
                return !$row["type"] ? "<i class=\"fad fa-clock  text-warning \" data-toggle=\"tooltip\" data-original-title=\"Sou um Teste\"></i> " . explode("@", $row["email"])[0] : explode("@", $row["email"])[0];
            }), array("db" => "password", "formatter" => function ($d, $row) {
                return $row["exField3"];
            }), array("db" => "created_at", "formatter" => function ($d, $row) {
                return !empty($row["regTime"]) ? date("d/m/Y H:i", strtotime($row["regTime"])) : "-";
            }), array("db" => "exp_date", "formatter" => function ($d, $row) {
                return !empty($row["endTime"]) ? date("d/m/Y H:i", strtotime($row["endTime"])) : "-";
            }), array("db" => "email", "formatter" => function ($d, $row) {
                return !empty(json_decode($row['exField4'], true)['email']) ? json_decode($row['exField4'], true)['email'] : "-";
            }),

            array("db" => "reseller_name", "formatter" => function ($d, $row) {
                return $row["exField1"];
            }), array("db" => "reseller_notes", "formatter" => function ($d, $row) {
                return "<span data-toggle=\"tooltip\" data-original-title=\"" . $row['serviceTag'] . "\">" . str_limit($row['serviceTag'], 15) . "</span>";
            }), array("db" => "status", "formatter" => function ($d, $row) {
                $status = "<span class=\"badge badge-light\" data-toggle=\"tooltip\" data-original-title=\"Bloqueie esse cliente para corrigir o status\" >Status Inválido</span>";
                if ($row['status'] == 1 || $row['status'] == -1) {
                    if ((is_null($row["endTime"]) || strtotime($row["endTime"]) > time())) {
                        $status = "<span class=\"badge badge-success\">Ativo</span>";
                    }
                } else {
                    if (strtotime($row["endTime"]) < time()) {
                        $status = "<span class=\"badge badge-warning\">Expirado</span>";
                    } else {
                        $status = "<span class=\"badge badge-danger\">Desativado</span>";
                    }
                }

                return $status;
            }), array("db" => "action", "searchable" => false, "formatter" => function ($d, $row) {
                return '
				<div class="actions text-center">
				    <a href="/p2p/edit/' . $row['id'] . '" class="btn btn-icon text-muted" data-toggle="tooltip" data-original-title="Editar Cliente" data-id="' . $row['id'] . '">
				        <i class="fad fa-user-edit" aria-hidden="true" style="font-size: 16px --fa-secondary-opacity: 1.0; --fa-secondary-color: dodgerblue;"></i>
				    </a>
                    <a href="#" class="btn btn-icon text-blue btfastmessage" data-toggle="tooltip" data-original-title="Mensagem Rápida" data-id="' . $row['id'] . '">
				        <i class="far fa-sticky-note" aria-hidden="true" style="font-size: 16px"></i>
				    </a>
				    <a href="#" class="btn btn-icon text-green btrenewplus" data-toggle="tooltip" data-original-title="Renovar vários meses - custo depende da quantidade de meses e telas." data-id="' . $row['id'] . '" data-text="Usuario: ' . explode("@", $row["email"])[0] . '">
				        <i class="fad fa-calendar-alt" aria-hidden="true" style="font-size: 16px"></i>
				    </a>
                    <a href="#" class="btn btn-icon btconvert" style="color: #00c4ff" data-toggle="tooltip" data-original-title="Converter para IPTV" data-id="' . $row['id'] . '" data-text="Usuario: ' . explode("@", $row["email"])[0] . '">
				        <i class="fad fa-exchange" aria-hidden="true" style="font-size: 16px"></i>
				    </a>
				    <a href="#" class="btn btn-icon text-yellow btblock" data-toggle="tooltip" data-original-title="Bloquear/Desbloquear" data-id="' . $row['id'] . '" data-text="Bloquear/desbloquear o usuário: ' . explode("@", $row["email"])[0] . '">
				        <i class="far fa-ban" aria-hidden="true" style="font-size: 16px"></i>
				    </a>
				    <a href="#" class="btn btn-icon text-red btdelete" data-toggle="tooltip" data-original-title="Deletar Cliente" data-id="' . $row['id'] . '" data-text="Deletar o cliente: ' . explode("@", $row["email"])[0] . '">
				        <i class="far fa-user-slash" aria-hidden="true" style="font-size: 16px"></i>
				    </a>
				</div>
				';
            })
        );
        $clients = isAdmin($reseller) ? getallp2pclientsadminwithoptions($start, $length, $columns, $search, $order_column_index, $order_type, $p2p) : getAllP2PClientsByOwnerWithOptions($reseller, $start, $length, $columns, $search, $order_column_index, $order_type, $p2p);
        return $clients;
    }
    return array();
}

function getAllP2PClientsAdminWithOptions($start = 0, $length = 10, $columns = array(), $search_value = "", $order_column_index = NULL, $order_type = "asc", $p2p = false, $expiring = false)
{
    $result = array("data" => array(), "recordsTotal" => 0, "recordsFiltered" => 0);
    include_once(__DIR__ . "/class/binstream.php");
    $binstream = new BinStream();
    // $all_clients = dataOutput($columns, getallp2pclientsadmin($p2p, $expiring));
    $all_clients = dataOutput($columns, $binstream->getusers("all"));
    if ($order_column_index !== NULL && isset($columns[$order_column_index]["db"])) {
        $order_column = $columns[$order_column_index]["db"];
        usort($all_clients, function ($a, $b) use ($order_column, $order_type) {
            if ($a[$order_column] === $b[$order_column]) {
                return 0;
            }
            if ($order_type == "asc") {
                return strip_tags($b[$order_column]) < strip_tags($a[$order_column]) ? 1 : -1;
            }
            return strip_tags($a[$order_column]) < strip_tags($b[$order_column]) ? 1 : -1;
        });
    }
    $current_index = 0;
    foreach ($all_clients as $current_client) {
        if (tryFind($current_client, $columns, $search_value)) {
            if ($start <= $current_index && count($result["data"]) < $length) {
                $result["data"][] = $current_client;
            }
            $current_index++;
        }
    }
    $result["recordsTotal"] = count($all_clients);
    $result["recordsFiltered"] = $current_index;
    return $result;
}

function getAllP2PClientsByOwnerWithOptions($user, $start = 0, $length = 10, $columns = array(), $search_value = "", $order_column_index = NULL, $order_type = "asc", $p2p = false, $expiring = false)
{
    $result = array("data" => array(), "recordsTotal" => 0, "recordsFiltered" => 0);
    $resellers = array($user["id"]);
    $resellers = array_merge($resellers, getAllResellersIdByOwnerID($user["id"]));
    include_once(__DIR__ . "/class/binstream.php");
    $binstream = new BinStream();
    $all_clients = dataOutput($columns, $binstream->getusers($resellers));
    // $all_clients = dataOutput($columns, getAllP2PClientsByOwner($user, $p2p, $expiring));
    if ($order_column_index !== NULL && isset($columns[$order_column_index]["db"])) {
        $order_column = $columns[$order_column_index]["db"];
        usort($all_clients, function ($a, $b) use ($order_column, $order_type) {
            if ($a[$order_column] === $b[$order_column]) {
                return 0;
            }
            if ($order_type == "asc") {
                return strip_tags($b[$order_column]) < strip_tags($a[$order_column]) ? 1 : -1;
            }
            return strip_tags($a[$order_column]) < strip_tags($b[$order_column]) ? 1 : -1;
        });
    }
    $current_index = 0;
    foreach ($all_clients as $current_client) {
        if (tryFind($current_client, $columns, $search_value)) {
            if ($start <= $current_index && count($result["data"]) < $length) {
                $result["data"][] = $current_client;
            }
            $current_index++;
        }
    }
    $result["recordsTotal"] = count($all_clients);
    $result["recordsFiltered"] = $current_index;
    return $result;
}

function iptvToP2P($user_id)
{
    $user = getClientById($user_id);
    if (!$user) {
        return "aui";
    }
    if (($user['enabled'] != 1) || ($user['admin_enabled'] != 1)) {
        return ['error' => 'Não é possível converter um usuário desativado.'];
    }
    if ($user['is_trial'] == 1) {
        return ['error' => 'Não é possível converter um usuário teste.'];
    }
    if ($user['exp_date'] < time()) {
        return ['error' => 'Não é possível converter um usuário expirado.'];
    }

    include_once(__DIR__ . "/class/binstream.php");
    $binstream = new BinStream();
    $package = getServerProperty("binstream_fast_test_package");
    $owner = getUserByID($user['member_id']);
    $logged_user = getLoggedUser();
    $reseller_notes = $user['reseller_notes'] . PHP_EOL . "Migrado para P2P em " . date("d/m/Y H:i:s") . " por " . getLoggedUser()['username'];
    $cost = 0;
    $data = [
        'name' => OFFICE_CONFIG['panel_id'],
        'email' => $user['username'] . "@" . OFFICE_CONFIG['binstream']['email'],
        'password' => $user['password'],
        'status' => 1,
        'type' => 1,
        'serviceTag' => $reseller_notes,
        'startTime' => gmdate("Y-m-d\TH:i:s\Z"),
        'endTime' => gmdate("Y-m-d\TH:i:s\Z", $user['exp_date']),
        'productId' => $package,
        'exField1' => $owner['username'],
        'exField2' => $owner['id'],
        'exField3' => $user['password'],
        'exField4' => json_encode(['email' => $user['email'], 'phone' => $user['phone']])
    ];

    $new_client = $binstream->create($data);
    if (isset($new_client['id'])) {
        deleteClient($user_id);
        $old_credits = $logged_user['credits'];
        $logged_user = getLoggedUser();
        $now_credits = $logged_user['credits'];
        // insertRegUserLog($logged_user['id'], $user['username'], $user['password'], '<b>Cliente IPTV Migrado para P2P</b> | Pacote: ' . $package . ' | Créditos: <font color="green">' . $old_credits . '</font> > <font color="red">' . $now_credits . '</font> | Custo: 0 Crédito', 'line', 'convert', $user['id'], $package, $cost, $now_credits, 'iptv', 'binstream');
        return true;
    }
    return ['error' => 'Não foi possível criar o usuário P2P.'];
}

function P2Ptoiptv($user_id)
{
    include_once(__DIR__ . "/class/binstream.php");
    $binstream = new BinStream();
    $user = $binstream->getuser($user_id);
    if ($user['status'] == 404) {
        return ['error' => 'Usuário P2P não encontrado!'];
    }
    if ($user['type'] == 0) {
        return ['error' => 'Não é possível converter um usuário teste.'];
    }
    if (($user['status'] == 0) || ($user['status'] == -1)) {
        return ['error' => 'Não é possível converter um usuário desativado.'];
    }
    if (strtotime($user['endTime']) < time()) {
        return ['error' => 'Não é possível converter um usuário expirado.'];
    }
    $cost = 0;
    $contact = json_decode($user['exField4'], true);
    $reseller_notes = $user['serviceTag'] . PHP_EOL . "Migrado para IPTV em " . date("d/m/Y H:i:s") . " por " . getLoggedUser()['username'];
    $package = getPackageByID(getServerProperty("fast_test_package"));
    $bouquets = $package['bouquets'];
    $logged_user = getLoggedUser();
    $new_client = insertClient($user['exField2'], explode("@", $user["email"])[0], $user['exField3'], $contact['phone'], $contact['email'], strtotime($user['endTime']), "", $reseller_notes, $bouquets, 1, 0);
    if (is_numeric($new_client)) {
        $binstream->deleteUser($user_id);
        $old_credits = $logged_user['credits'];
        $logged_user = getLoggedUser();
        $now_credits = $logged_user['credits'];
        // insertRegUserLog($logged_user['id'], explode("@", $user["email"])[0], $user['exField3'], '<b>Cliente P2P Migrado para IPTV</b> | Pacote: ' . $package['package_name'] . ' | Créditos: <font color="green">' . $old_credits . '</font> > <font color="red">' . $now_credits . '</font> | Custo: 0 Crédito', 'binstream', 'convert', $new_client, $package['id'], $cost, $now_credits, 'binstream', 'iptv');
        return true;
    }
    return ['error' => 'Não foi possível criar o usuário IPTV.'];
}

function hasPermissionResource($user_id, $resource, $default = "disabled")
{
    $user = getUserById($user_id);
    if (isAdmin($user)) {
        return true;
    }

    global $databaseOffice;
    $databaseOffice->where("property", $resource . "_enabled");
    $databaseOffice->where("userid", $user_id);
    $result = $databaseOffice->getOne('user_properties');
    if (is_null($result) || $result['value'] == "enabled") {
        return true;
    }

    return false;
}

function binStreamEnabled($byPassCache = false)
{
    if (OFFICE_CONFIG['binstream']['enabled']) {
        global $redis;
        $key = OFFICE_CONFIG['panel_id'] . "_binstream_check";
        if (!$byPassCache) {
            $cached_result = $redis->get($key);
            if ($cached_result) {
                return json_decode($cached_result, true);
            }
        }
        $result = checkBinStreamConfig(OFFICE_CONFIG['binstream']['url'], OFFICE_CONFIG['binstream']['token']);
        $redis->setex($key, 600, json_encode($result));
        return $result;
    }
    return false;
}

function checkBinStreamConfig($url, $token)
{
    include_once __DIR__ . "/class/CurlRequest.php";

    $request_url = $url . 'product?t=' . $token . "&select=id%20name";
    $CurlRequest = new CurlRequest($request_url, "GET");
    $response = $CurlRequest->makeRequest();

    if ($response['http_code'] === 200) {
        return ['success' => true, 'message' => 'Configuração válida.'];
        return true;
    } else if ($response['http_code'] === 403) {
        return ['success' => false, 'message' => 'Token Inválido (403)'];
    } else if ($response['http_code'] === 404) {
        return ['success' => false, 'message' => 'URL Inválida (404)'];
    } else {
        return ['success' => false, 'message' => 'Erro desconhecido'];
    }
}

function clearServerCache($resource = "*")
{
    global $redis;
    $redis->del($redis->keys(OFFICE_CONFIG['panel_id'] . "_server_property_" . $resource));
}

function clearUserCache($user_id, $resource = "*")
{
    global $redis;
    $redis->del($redis->keys(OFFICE_CONFIG['panel_id'] . "_userid_" . $user_id . "_" . $resource));
}

function binCountResellerClients($resellerId)
{
    $resellers = getAllResellersIdByOwnerID($resellerId);
    array_push($resellers, $resellerId);

    include_once(__DIR__ . "/class/binstream.php");
    $binstream = new BinStream();
    $totalClients = $binstream->countUsers($resellers, "active");
    return $totalClients;
}

// function telegramSendImage($chat_id, $image_path, $caption, $token)
// {
//     new Longman\TelegramBot\Telegram($token, "@NPanel_Updates_bot");
// $data = [
//     'chat_id' => $chat_id,
//     'photo' => $image_path,
//     'caption' => $caption,
//     'parse_mode' => 'html'
// ];

//     return Request::sendPhoto([
//         'chat_id' => $chat_id,
//         'photo' => Request::encodeFile($image_path),
//         'caption' => $caption,
//         'parse_mode' => 'html'
//     ]);
// }

function generate_uuid($data = null)
{
    // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
    $data = $data ?? random_bytes(16);
    assert(strlen($data) == 16);

    // Set version to 0100
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    // Set bits 6-7 to 10
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

    // Output the 36 character UUID.
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function testKeyInfo($key)
{
    global $databaseOffice;
    $databaseOffice->where("value", $key);
    $result = $databaseOffice->getOne('user_properties');
    return $result;
}

function addChatBotRule(int $owner, string $rule_type, string $rule_action, string $response, array $messages)
{
    global $databaseOffice;

    $data = array(
        'rule_type' => $rule_type,
        'rule_action' => $rule_action,
        'response' => $response,
        'reseller' => $owner
    );
    $chatbot_id = $databaseOffice->insert('chatbot', $data);

    foreach ($messages as $message) {
        if (!empty($message)) {
            $data = array(
                'chatbot_id' => $chatbot_id,
                'reseller' => $owner,
                'message' => $message
            );
            $databaseOffice->insert('chatbot_messages', $data);
        }
    }
    if ($chatbot_id) {
        return true;
    } else {
        return false;
    }
}

function updateChatBotRule(int $owner, int $chatbot_id, string $rule_type, string $rule_action, string $response, array $messages)
{
    global $databaseOffice;

    $data = array(
        'rule_type' => $rule_type,
        'rule_action' => $rule_action,
        'response' => $response,
        'reseller' => $owner
    );
    $databaseOffice->where('id', $chatbot_id);
    $databaseOffice->where('reseller', $owner);
    $databaseOffice->update('chatbot', $data);

    $databaseOffice->where('chatbot_id', $chatbot_id);
    $databaseOffice->where('reseller', $owner);
    $databaseOffice->delete('chatbot_messages');

    foreach ($messages as $message) {
        if (!empty($message)) {
            $data = array(
                'chatbot_id' => $chatbot_id,
                'reseller' => $owner,
                'message' => $message
            );
            $databaseOffice->insert('chatbot_messages', $data);
        }
    }
    return true;
}

function incrementChatbotRuleRuns($chatbot_id)
{
    global $databaseOffice;

    $data = array(
        'runs' => $databaseOffice->inc(1)
    );

    $databaseOffice->where('id', $chatbot_id);
    $databaseOffice->update('chatbot', $data);
}

function getAllChatbotRulesByReseller($reseller_id)
{
    global $databaseOffice;
    $databaseOffice->where("reseller", $reseller_id);
    $chatbot = $databaseOffice->get('chatbot');

    foreach ($chatbot as &$row) {
        $databaseOffice->where("reseller", $reseller_id);
        $databaseOffice->where("chatbot_id", $row['id']);
        $chatbot_messages = $databaseOffice->get('chatbot_messages');

        $messages = array();
        foreach ($chatbot_messages as $message) {
            $messages[] = $message['message'];
        }

        $row['messages'] = $messages;
    }

    return $chatbot;
}

//get chatbot rule by id
function getChatbotRuleById($chatbot_id)
{
    global $databaseOffice;
    $databaseOffice->where("id", $chatbot_id);
    $chatbot = $databaseOffice->getOne('chatbot');

    $databaseOffice->where("chatbot_id", $chatbot_id);
    $chatbot_messages = $databaseOffice->get('chatbot_messages');

    $messages = array();
    foreach ($chatbot_messages as $message) {
        $messages[] = $message['message'];
    }

    $chatbot['messages'] = $messages;

    return $chatbot;
}

//togle chatbot rule status
function toggleChatbotRuleStatus($chatbot_id, $reseller_id)
{
    global $databaseOffice;
    $data = array(
        'status' => $databaseOffice->not()
    );

    $databaseOffice->where('id', $chatbot_id);
    $databaseOffice->where('reseller', $reseller_id);
    $databaseOffice->update('chatbot', $data);

    return true;
}

//delete chatbot rule by id
function deleteChatbotRuleById($chatbot_id, $reseller_id)
{
    global $databaseOffice;
    $databaseOffice->where("id", $chatbot_id);
    $databaseOffice->where("reseller", $reseller_id);
    $databaseOffice->delete('chatbot');
    return true;
}

function getChatbotRulesTable($userid, $start, $length, $search, $order_column_index, $order_type)
{
    $reseller = getuserbyid($userid);
    if ($reseller) {
        $columns = array(
            array("db" => "id"),
            array("db" => "rule_type", "formatter" => function ($d, $row) {
                return $row["rule_type"] == "equals" ? "Igual" : "Contém";
            }),
            array("db" => "rule_action", "formatter" => function ($d, $row) {
                switch ($row["rule_action"]) {
                    case 'text':
                        $rule_action = "Enviar Texto";
                        break;
                    case 'test_iptv':
                        $rule_action = "Enviar Teste IPTV";
                        break;
                    case 'test_code':
                        $rule_action = "Enviar Teste Código";
                        break;
                    case 'test_binstream':
                        $rule_action = "Enviar Teste Binstream";
                        break;
                    default:
                        $rule_action = "Enviar Texto";
                        break;
                }
                return $rule_action;
            }),
            array("db" => "messages", "formatter" => function ($d, $row) {
                $messages = "";
                foreach ($row["messages"] as $message) {
                    $messages .= "<span class=\"badge badge-info\">$message</span> ";
                }
                return $messages;
            }),
            array("db" => "runs"),
            array("db" => "status", "formatter" => function ($d, $row) {
                return $row["status"] ? "<span class=\"badge badge-success\">Ativo</span>" : "<span class=\"badge badge-danger\">Desativado</span>";
            }),
            array("db" => "action", "searchable" => false, "formatter" => function ($d, $row) {
                $action = '<div class="actions text-center">';
                $action .= '<a href="/chatbot/edit/' . $row["id"] . '" class="btn btn-icon text-muted" data-toggle="tooltip" data-original-title="Editar" data-id="' . $row["id"] . '"><i class="fad fa-pencil" aria-hidden="true" style="font-size: 16px"></i></a>';
                $action .= '<a href="#" class="btn btn-icon text-yellow bttoggle" data-toggle="tooltip" data-original-title="Ativar/Desativar" data-id="' . $row["id"] . '" data-text="Ativar/Desativar a regra"><i class="fad fa-power-off" aria-hidden="true" style="font-size: 16px"></i></a>';
                $action .= '<a href="#" class="btn btn-icon text-danger btdelete" data-toggle="tooltip" data-original-title="Excluir" data-id="' . $row["id"] . '" data-text="Excluir a regra"><i class="fad fa-trash" aria-hidden="true" style="font-size: 16px"></i></a>';
                $action .= '</div>';

                return $action;
            })
        );

        $tickets = getAllChatbotRules($reseller, $start, $length, $columns, $search, $order_column_index, $order_type);
        return $tickets;
    }
    return array();
}

function getAllChatbotRules($reseller, $start = 0, $length = 10, $columns = array(), $search_value = "", $order_column_index = NULL, $order_type = "asc")
{
    $result = array("data" => array(), "recordsTotal" => 0, "recordsFiltered" => 0);
    $all_chatbot_rules = dataoutput($columns, getAllChatbotRulesByReseller($reseller["id"]));
    if ($order_column_index !== NULL && isset($columns[$order_column_index]["db"])) {
        $order_column = $columns[$order_column_index]["db"];
        usort($all_chatbot_rules, function ($a, $b) use ($order_column, $order_type) {
            if ($a[$order_column] === $b[$order_column]) {
                return 0;
            }
            if ($order_type == "asc") {
                return strip_tags($b[$order_column]) < strip_tags($a[$order_column]) ? 1 : -1;
            }
            return strip_tags($a[$order_column]) < strip_tags($b[$order_column]) ? 1 : -1;
        });
    }
    $current_index = 0;
    foreach ($all_chatbot_rules as $current_rule) {
        if (tryfind($current_rule, $columns, $search_value)) {
            if ($start <= $current_index && count($result["data"]) < $length) {
                $result["data"][] = $current_rule;
            }
            $current_index++;
        }
    }
    $result["recordsTotal"] = count($all_chatbot_rules);
    $result["recordsFiltered"] = $current_index;
    return $result;
}

function chatbotCreateTest($owner, $type, $chatbot_id)
{
    if (!hasPermissionResource($owner['id'], $type)) {
        return ['error' => 'Tipo de teste não disponível no momento!'];
    }

    if ($type == "code" && !getServerProperty('code_status', 1)) {
        return ['error' => 'Código não disponível no momento!'];
    }

    if ((!isAdmin($owner) && !isPartner($owner)) && ($owner['credits'] < getServerProperty('test_min_credits', 0))) {
        return ['error' => 'Teste desativado temporariamente!'];
    }

    $result = createFastTestDash($owner['id'], $type, "Via Chatbot");
    if ($result != false) {
        return htmlspecialchars_decode(json_encode(TemplateReplace($owner['id'], $result['username'], $result['password'], $result['duration'], $type, $chatbot_id)), ENT_QUOTES);;
    }
    return ['error' => 'Falha ao criar teste, tente novamente mais tarde!'];
}

function purifyHTML($input, $allowedTags = '')
{

    if (is_array($input)) {
        return purifyHTMLArray($input, $allowedTags);
    }

    if (is_null($input) || strtoupper($input) == "NULL") {
        return NULL;
    }

    $config = HTMLPurifier_Config::createDefault();
    $config->set('HTML.Allowed', $allowedTags);
    $config->set('CSS.AllowedProperties', 'color, background-color, font-size, font-weight'); // Adicione outras propriedades CSS conforme necessário
    $config->set('HTML.AllowedAttributes', 'target, href, title, class, style'); // Adicione outros atributos HTML conforme necessário

    $purifier = new HTMLPurifier($config);

    $filteredInput = $purifier->purify($input);

    $preg_replace = preg_replace('/<\?php.*\?>/i', '', $filteredInput);

    return $preg_replace;
}

function purifyHTMLArray($array, $allowedTags = '')
{
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $array[$key] = purifyHTMLArray($value, $allowedTags);
        } else {
            $array[$key] = purifyHTML($value, $allowedTags);
        }
    }
    return $array;
}


function maintenanceEnabled()
{
    $maintenance = json_decode(getServerProperty('maintenance', '{"status":0,"message":"","button_text":"","button_link":""}', true), true);

    return $maintenance;
}

function getSimpleResellerList($logged_user_id, $search)
{
    $user = getUserById($logged_user_id);
    $resellers = getAllResellersByOwnerID($user['id']);

    global $database;
    if (!isAdmin($user)) {
        $resellers = getAllResellersByOwnerID($logged_user_id);
        $database->where("id", $resellers, "IN");
    }
    $database->where("username", "%" . $search . "%", "LIKE");
    $database->orderBy("id", "ASC");
    $resellers = $database->get("users", null, array("id", "username", "owner_id"));

    return $resellers;
}
