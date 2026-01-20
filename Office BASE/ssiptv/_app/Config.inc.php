<?php
require_once('Helpers/Curl.class.php');
require_once(__DIR__ . '/../../sys/functions.php');

define("DNS_URL", getServerDNS());
define("SSIPTV_LIVE_ENABLED", "1");
define("SSIPTV_MOVIE_ENABLED", "1");
define("SSIPTV_SERIE_ENABLED", "1");
define("SSIPTV_LIVE_NAME", "CANAIS");
define("SSIPTV_MOVIE_NAME", "FILMES");
define("SSIPTV_SERIE_NAME", "SERIES");
define("SSIPTV_LIVE_IMAGE_URL", "https://i.imgur.com/JCcgEeo.png");
define("SSIPTV_MOVIE_IMAGE_URL", "https://i.imgur.com/vnPmfhz.png");
define("SSIPTV_SERIE_IMAGE_URL", "https://i.imgur.com/vJD9S1U.png");

define('WS_ACCEPT', 'accept');
define('WS_INFOR', 'infor');
define('WS_ALERT', 'alert');
define('WS_ERROR', 'error');

include_once('Conn/Conn.class.php');
include_once('Conn/Read.class.php');

function PHPErro($ErrNo, $ErrMsg, $ErrFile, $ErrLine)
{
  $CssClass = ($ErrNo == E_USER_NOTICE ? WS_INFOR : ($ErrNo == E_USER_WARNING ? WS_ALERT : ($ErrNo == E_USER_ERROR ? WS_ERROR : $ErrNo)));
  echo "<p class=\"trigger {$CssClass}\">";
  echo "<b>Erro na Linha: #{$ErrLine} ::</b> {$ErrMsg}<br>";
  echo "<small>{$ErrFile}</small>";
  echo "<span class=\"ajax_close\"></span></p>";

  if ($ErrNo == E_USER_ERROR) :
    die;
  endif;
}

set_error_handler('PHPErro');

function WSErro($ErrMsg, $ErrNo, $ErrDie = null)
{
  $CssClass = ($ErrNo == E_USER_NOTICE ? WS_INFOR : ($ErrNo == E_USER_WARNING ? WS_ALERT : ($ErrNo == E_USER_ERROR ? WS_ERROR : $ErrNo)));
  echo "<p class=\"trigger {$CssClass}\">{$ErrMsg}<span class=\"ajax_close\"></span></p>";

  if ($ErrDie) :
    die;
  endif;
}

function ToastError($ErrMsg, $ErrNo = null)
{
  $CssClass = ($ErrNo == E_USER_NOTICE ? 'trigger_info' : ($ErrNo == E_USER_WARNING ? 'trigger_alert' : ($ErrNo == E_USER_ERROR ? 'trigger_error' : 'trigger_success')));
  return "<div class='trigger trigger_ajax {$CssClass}'>{$ErrMsg}<span class='ajax_close'></span><span class='ajax_close'></span><div class='trigger_progress'></div></div>";
}

if (!function_exists('getallheaders')) {
  function getallheaders()
  {
    $headers = [];
    foreach ($_SERVER as $name => $value) {
      if (substr($name, 0, 5) == 'HTTP_') {
        $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
      }
    }
    return $headers;
  }
}

function getAuthorizationHeader()
{
  $headers = null;
  if (isset($_SERVER['Authorization'])) {
    $headers = trim($_SERVER["Authorization"]);
  } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
    $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
  } elseif (function_exists('apache_request_headers')) {
    $requestHeaders = apache_request_headers();
    // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
    $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
    //print_r($requestHeaders);
    if (isset($requestHeaders['Authorization'])) {
      $headers = trim($requestHeaders['Authorization']);
    }
  }
  return $headers;
}

function http_response_error(array $message)
{
  echo json_encode([
    "statusCode" => http_response_code(),
    "timestamp" => date("Y-m-d H:m:i"),
    "method" => $_SERVER['REQUEST_METHOD'],
    "path" => $_SERVER['REQUEST_URI'],
    "data" => $message
  ]);

  die;
}
