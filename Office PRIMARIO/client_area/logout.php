<?php
include_once('../sys/functions.php');

session_start();
session_unset();
logoutClient();
header('location: ../index.php?result=logged_out');
