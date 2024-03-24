<?php

    session_start();

    define('HOST','localhost');
    define('USER','root');
    define('PASSWORD','');
    define('DATABASE','attendance_system');
    define('APP_NAME', 'Attendance System');

    define('ROOT', '');
    define('CSS_DIR', ROOT . 'assets/css/');
    define('JS_DIR', ROOT . 'assets/js/');

    $con = mysqli_connect(HOST, USER, PASSWORD, DATABASE) or die("FAILED TO CONNECT TO DB");

    require_once "functions.php";

    $userData = authenticateUser();
    if(!$userData) {
        // user not logged in
        // Show login page
        require_once "login.php";

        die();
    }
?>