<?php

    function authenticateUser() {
        if(!isset($_SESSION['login_user']) || empty($_SESSION['login_user'])) return false;

        $id = $_SESSION['login_user'];

        return isUserExists($id);
    }

    function validateCredentials($user, $pass) {
        global $con;
        
        $user = mysqli_real_escape_string($con, trim($user));
        $pass = mysqli_real_escape_string($con, trim($pass));

        $pass = sha1($pass);

        $query = mysqli_query($con, "SELECT * FROM users WHERE user = '$user' AND password = '$pass'");
        if(mysqli_num_rows($query) == 0) return false;

        return mysqli_fetch_assoc($query);
    }

    function isUserExists($id) {
        global $con;

        $id = mysqli_real_escape_string($con, trim($id));
        if(empty($id)) return false;

        $query = mysqli_query($con, "SELECT * FROM users WHERE user = '$id'");
        
        if(mysqli_num_rows($query) == 0) return false;

        return mysqli_fetch_assoc($query);
    }

    


?>