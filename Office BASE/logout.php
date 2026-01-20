<?php
session_start();
include_once('./sys/functions.php');
//apaga cache do redis
clearUserCache($_SESSION['__l0gg3d_us3r__']);

session_unset();
unset($_COOKIE['username']);
unset($_COOKIE['password']);
setcookie('username', "", -3600, '/');
setcookie('password', "", -3600, '/');

header('location: /');
