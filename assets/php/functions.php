<?php

function authenticateUser()
{
    if (!isset ($_SESSION['login_user']) || empty ($_SESSION['login_user']))
        return false;

    $user = $_SESSION['login_user'];

    return isUserExists($user);
}

function validateCredentials($user, $pass)
{
    global $con;

    $user = mysqli_real_escape_string($con, trim($user));
    $pass = mysqli_real_escape_string($con, trim($pass));

    $pass = sha1($pass);

    $query = mysqli_query($con, "SELECT * FROM users WHERE user = '$user' AND password = '$pass'");
    if (mysqli_num_rows($query) == 0)
        return false;

    return mysqli_fetch_assoc($query);
}

function isUserExists($id, $getPermisison = true)
{
    global $con;

    $id = mysqli_real_escape_string($con, trim($id));
    if (empty ($id))
        return false;

    $query = mysqli_query($con, "SELECT * FROM users WHERE user = '$id'");

    if (mysqli_num_rows($query) == 0)
        return false;

    $userData = mysqli_fetch_assoc($query);
    $permissions = [];

    if ($getPermisison) {
        $query = mysqli_query($con, "SELECT GROUP_CONCAT(p.name) as permissions FROM role_has_permission r inner join permissions p on r.permission = p.id GROUP BY r.role HAVING r.role = '" . $userData['role'] . "'");

        $permissions['permissions'] = explode(",", mysqli_fetch_assoc($query)['permissions']);
    }

    return array_merge($userData, $permissions);
}

function hasPermission($permission)
{
    global $userData;

    return in_array($permission, $userData['permissions']);
}

?>