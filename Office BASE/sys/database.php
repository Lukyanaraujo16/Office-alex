<?php
require_once(__DIR__ . '/class/MysqliDB.php');

if (!defined('OFFICE_CONFIG')) {
  $DB_INFO = json_decode(file_get_contents(__DIR__ . "/../../dbinfo.json"), true);
  define("OFFICE_CONFIG", $DB_INFO);
}

$database = new MysqliDb(array(
  'host' => OFFICE_CONFIG['remote_db']['hostname'],
  'username' => OFFICE_CONFIG['remote_db']['username'],
  'password' => OFFICE_CONFIG['remote_db']['password'],
  'db' => OFFICE_CONFIG['remote_db']['database'],
  'port' => OFFICE_CONFIG['remote_db']['port'],
  'charset' => "utf8"
));

$databaseOffice = new MysqliDb(array(
  'host' => OFFICE_CONFIG['office_db']['hostname'],
  'username' => OFFICE_CONFIG['office_db']['username'],
  'password' => OFFICE_CONFIG['office_db']['password'],
  'db' => OFFICE_CONFIG['office_db']['database'],
  'port' => OFFICE_CONFIG['office_db']['port'],
  'charset' => "utf8"
));
