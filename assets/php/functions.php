<?php

function authenticateUser()
{
    if (!isset($_SESSION['login_user']) || empty($_SESSION['login_user']))
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
    if (empty($id))
        return false;

    $query = mysqli_query($con, "SELECT * FROM users WHERE user = '$id'");

    if (mysqli_num_rows($query) == 0)
        return false;

    $userData = mysqli_fetch_assoc($query);
    $permissions = [];

    if ($getPermisison) {
        $query = mysqli_query($con, "SELECT GROUP_CONCAT(p.name) as permissions FROM role_has_permission r inner join permissions p on r.permission = p.id GROUP BY r.role HAVING r.role = '" . $userData['role'] . "'");

        if (mysqli_num_rows($query) > 0)
            $permissions['permissions'] = explode(",", mysqli_fetch_assoc($query)['permissions']);
    }

    return array_merge($userData, $permissions);
}

function hasPermission($permission)
{
    global $userData;

    return in_array($permission, $userData['permissions']);
}

function constructQueryData($data)
{
    if (count($data) > 0)
        return "WHERE " . implode(" AND ", $data);
    return "";
}

function getUserClasses()
{
    global $userData, $con;

    $queryData = [];
    if (in_array(CAN_SEE_ALL_CLASS, $userData['permissions'])) {

    } else {
        $queryData[] = "teaches.teacher_id = '" . $userData['id'] . "'";
    }

    $queryData = constructQueryData($queryData);

    $query = mysqli_query($con, "SELECT classes.* FROM teaches RIGHT JOIN classes ON teaches.class_id = classes.id $queryData GROUP BY classes.id");

    $classes = [];
    while ($row = mysqli_fetch_assoc($query))
        $classes[] = $row;
    return $classes;
}

function getUserSubjects()
{
    global $userData, $con;

    $queryData = [];
    if (in_array(CAN_SEE_ALL_SUBJECT, $userData['permissions'])) {

    } else {
        $queryData[] = "teaches.teacher_id = '" . $userData['id'] . "'";
    }

    $queryData = constructQueryData($queryData);

    $query = mysqli_query($con, "SELECT subject.* FROM teaches RIGHT JOIN subject ON teaches.subject_id = subject.subject_code $queryData  GROUP BY subject.subject_code");

    $classes = [];
    while ($row = mysqli_fetch_assoc($query))
        $classes[] = $row;
    return $classes;
}

function getUserClassSubject($classId = '', $subjectCode = '')
{
    global $userData, $con;

    $queryData = [];
    if (in_array(CAN_SEE_ALL_CLASS, $userData["permissions"]) && in_array(CAN_SEE_ALL_SUBJECT, $userData["permissions"])) {

    } else {
        $queryData[] = "teaches.teacher_id = '" . $userData['id'] . "'";
    }

    if (!empty($classId))
        $queryData[] = "teaches.class_id = '$classId'";
    if (!empty($subjectCode))
        $queryData[] = "teaches.subject_id = '$subjectCode'";

    $queryData = constructQueryData($queryData);

    $query = mysqli_query($con, "
    SELECT teaches.id as teaches_id, subject.subject_code, subject.name as subject_name, classes.id = class_id, classes.department, classes.batch, classes.semester
    FROM teaches INNER JOIN classes INNER JOIN subject ON teaches.class_id = classes.id AND teaches.subject_id = subject.subject_code $queryData");

    $classes = [];
    while ($row = mysqli_fetch_assoc($query))
        $classes[] = $row;
    return $classes;
}

?>