<?php
include_once "functions.php";
logoutAdmin();
header('location: ./index.php?result=logged_out');
