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

function isUserExists($id, $getPermission = true)
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

    if ($getPermission) {
        $query = mysqli_query($con, "SELECT GROUP_CONCAT(p.name) as permissions FROM role_has_permission r inner join permissions p on r.permission = p.id GROUP BY r.role HAVING r.role = '" . $userData['role'] . "'");

        if (mysqli_num_rows($query) > 0)
            $permissions['permissions'] = explode(",", mysqli_fetch_assoc($query)['permissions']);
    }

    return array_merge($userData, $permissions);
}

function hasPermission($permission)
{
    global $userData;

    if (!defined($permission))
        return false;

    return in_array(constant($permission), $userData['permissions']);
}

function constructQueryData($data)
{
    if (count($data) > 0)
        return "WHERE " . implode(" AND ", $data);
    return "";
}

function getMysqlResultToArray($mysqlResult)
{
    $result = [];

    while ($row = mysqli_fetch_assoc($mysqlResult))
        $result[] = $row;

    return $result;
}

function getUserClasses()
{
    global $userData, $con;

    $queryData = [];
    if (hasPermission("CAN_SEE_ALL_CLASS")) {

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
    if (hasPermission('CAN_SEE_ALL_SUBJECT')) {

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
    if (hasPermission('CAN_SEE_ALL_CLASS') && hasPermission('CAN_SEE_ALL_SUBJECT')) {

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

    return getMysqlResultToArray($query);
}

function getAllPermissions(): bool|mysqli_result
{
    global $con;

    $query = mysqli_query($con, "SELECT * FROM permissions ORDER BY name ASC");

    return ($query);
}

function formatQueryToStr($str, $query)
{
    # in this I will take $str which has the format list <option value="{id}">{name}</option> and $mysqlResult is the mysqli_result contains the result from database i want to loop through each value in resutl and return it in the format $str

    $result = '';

    while ($row = mysqli_fetch_assoc($query)) {
        $currentItem = preg_replace_callback_array(
            [
                '/\{([a-zA-Z0-9]+)\}/' => function ($pattern) use ($row) {
                    $key = $pattern[1];

                    if (array_key_exists($key, $row)) {
                        return $row[$key];
                    }

                    return $pattern[0];
                },
            ],
            $str,
        );

        print_r($currentItem);


        $result .= $currentItem;
    }

    return $result;
}


/* checking if any of the value is missing in REQUEST (ALL FIELD REQUIRED) */
function processRequestData(...$names)
{
    // This block will check if $names fields are present in request or not and if exists then, return there values in the order
    global $con;

    $result = [];
    foreach ($names as $name) {
        if (
            !isset($_REQUEST[$name]) ||
            (
                (
                    $item = mysqli_real_escape_string($con, trim($_REQUEST[$name]))
                ) &&
                empty($item)
            )
        ) {
            http_response_code(403);
            die(json_encode([
                'status' => 'error',
                'message' => 'All fields are required'
            ]));
        } else {
            $result[] = $item;
        }
    }

    return $result;
}
?>