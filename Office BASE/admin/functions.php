<?php

define("ADMIN_DEBUG", false);
ini_set("log_errors", 1);

if (debugEnabled()) {
  error_reporting(32767);
  ini_set("display_errors", 1);
} else {
  error_reporting(0);
  ini_set("display_errors", 0);
}
date_default_timezone_set("America/Sao_Paulo");

$DB_INFO = GetDBData();
//define("OFFICE_CONFIG", $DB_INFO);

if (file_exists("../sys/config.php")) {
  include_once "../sys/config.php";
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

function getOfficeConnection()
{
  return DB::getInstance()->getConnection(OFFICE_CONFIG['office_db']['hostname'], OFFICE_CONFIG['office_db']['port'], OFFICE_CONFIG['office_db']['database'], OFFICE_CONFIG['office_db']['username'], OFFICE_CONFIG['office_db']['password']);
}

function getUser()
{
  $PDO = getofficeconnection();
  if ($PDO !== NULL) {
    $sql = "SELECT `value` FROM `office_properties` WHERE `property` = 'admin_data';";
    $database = $PDO->prepare($sql);
    if ($database->execute()) {
      $result = $database->fetch(PDO::FETCH_ASSOC);
      if (isset($result['value'])) {
        return json_decode($result['value'], true);
      }
    }
  }
  return false;
}

function updateUser($data)
{
  $PDO = getofficeconnection();
  if ($PDO !== NULL) {
    if ((!empty($data['username'])) and (!empty($data['password']))) {
      $data = json_encode($data);
      $sql = "UPDATE `office_properties` SET `value` = :_value WHERE `property` = 'admin_data';";
      $database = $PDO->prepare($sql);
      $database->bindParam(":_value", $data, PDO::PARAM_STR, 10000);
      if ($database->execute()) {
        return true;
      }
    }
    return false;
  }
  return "Usuário e/ou senha não pode estar em branco!";
}

function startSession()
{
  if (session_status() == PHP_SESSION_NONE) {
    session_start();
  }
}

function isLoggedAdmin($destination = "./index.php")
{
  startsession();
  if (!isset($_SESSION["__logged_user__"])) {
    header("Location: " . $destination);
    exit;
  }
}

function loginAdmin($username, $password)
{
  $user = getUser();
  print_r($user);
  if (($username == $user['username']) and ($password == $user['password'])) {
    startsession();
    $_SESSION["__logged_user__"] = true;
    return 1;
  } else {
    return 3;
  }
}

function logoutAdmin()
{
  startsession();
  unset($_SESSION);
  SESSION_DESTROY();
}

function GetDBData()
{
  $DB_INFO = json_decode(file_get_contents(__DIR__ . "/../../dbinfo.json"), true);
  defined("OFFICE_CONFIG") or define("OFFICE_CONFIG", $DB_INFO);

  return OFFICE_CONFIG;
}

function debugEnabled()
{
  if (defined("ADMIN_DEBUG") && ADMIN_DEBUG) {
    return true;
  }
  return false;
}
