<?php
# importin gautoloader
require_once(__DIR__ . '/../../vendor/autoload.php');

# importing ENV variable
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

session_start();
# GROQ api
define('GROQ_API_KEY', $_ENV['GROQ_API_KEY'] ?? '');

# Mysql connection
define('HOST', $_ENV['MYSQL_HOST'] ?? '');
define('USER', $_ENV['MYSQL_USER'] ?? '');
define('PASSWORD', $_ENV['MYSQL_PASS'] ?? '');
define('DATABASE', $_ENV['MYSQL_DB'] ?? '');

# default app name
define('APP_NAME', 'Attendance System');

# For navigating in file system
define('ROOT', '');
define('CSS_DIR', ROOT . 'assets/css/');
define('JS_DIR', ROOT . 'assets/js/');

# preparing mysql connection
$con = mysqli_connect(HOST, USER, PASSWORD, DATABASE) or die("FAILED TO CONNECT TO DB");

# importing necessary file throughout the app
require_once "permission_variable.php";
require_once "functions.php";

# authenticating user
$userData = authenticateUser();
if (!$userData) {
    // user not logged in
    // Show login page
    require_once "login.php";

    die();
}
?>