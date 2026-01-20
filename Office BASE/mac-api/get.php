<?php
include_once ('../sys/functions.php');

$server_dns = getServerDNS();

header("HTTP/1.1 302 Moved Temporarily");
header("Location: {$server_dns}/get.php?{$_SERVER['QUERY_STRING']}");
