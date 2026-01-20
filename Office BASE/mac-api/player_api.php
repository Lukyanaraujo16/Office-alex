<?php
include_once ('../sys/functions.php');
//error_reporting(32767);
//ini_set("display_errors", 1);

$server_dns = getServerDNS();

if (isset($_GET['action']) && !empty( $_GET['action'] )){
    header("Location: {$server_dns}/player_api.php?{$_SERVER['QUERY_STRING']}", true, 302);
    exit;
}else{
    $username = $_GET['username'];
    $password = $username;
    $memberID = intval($_GET['reg_id']);
    $email = "";
    $phone = "";
    $default_pakage = getServerProperty('fast_test_package');
    $package = getPackageByID($default_pakage);
    $duration = $package["trial_duration"] . " " . $package["trial_duration_in"];
    if (!existClient($username)){
        $result = createClient($memberID, $username, $password, $phone, $email, $duration, $package["bouquets"], "MAC API", 1);
        sleep(2);
        if($result != false){
            header("Location: {$server_dns}/player_api.php?{$_SERVER['QUERY_STRING']}", true, 302);
            exit;
        }
    }else{
        header("Location: {$server_dns}/player_api.php?{$_SERVER['QUERY_STRING']}", true, 302);
        exit;
    }
}
