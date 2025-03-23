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
    if (hasPermission("CAN_SEE_ALL_CLASSES")) {

    } else if (hasPermission('CAN_SEE_CLASS')) {

        if (isset($userData['role']) && $userData['role'] != 'student')
            $queryData[] = "faculty_id = '" . $userData['id'] . "'";
        else
            $queryData[] = "enrollment_number = '" . $userData['enrollment_number'] . "'";
    }

    $queryData = constructQueryData($queryData);

    if (isset($userData['role']) && $userData['role'] != 'student')
        $query = mysqli_query($con, "SELECT classes.* FROM teaches RIGHT JOIN classes ON teaches.class_id = classes.id $queryData GROUP BY classes.id");
    else
        $query = mysqli_query($con, "SELECT classes.* FROM classes JOIN students ON classes.id = students.class_id $queryData GROUP BY classes.id");

    return $query;
}

function getUserSubjects()
{
    global $userData, $con;

    $queryData = [];
    if (hasPermission('CAN_SEE_ALL_SUBJECTS')) {

    } else if (hasPermission('CAN_SEE_SUBJECT')) {
        if (isset($userData['role']) && $userData['role'] != 'student')
            $queryData[] = "faculty_id = '" . $userData['id'] . "'";
        else
            $queryData[] = "enrollment_number = '" . $userData['enrollment_number'] . "'";
    }

    $queryData = constructQueryData($queryData);

    if (isset($userData['role']) && $userData['role'] != 'student')
        $query = mysqli_query($con, "SELECT subject_code, subject as name FROM lectures $queryData GROUP BY subject_code");
    else
        $query = mysqli_query($con, "SELECT subject_code, subject as name FROM student_lectures $queryData GROUP BY subject_code");

    return $query;
}

function getUserClassSubject($classId = '', $subjectCode = '')
{
    global $userData, $con;

    $queryData = [];
    if (hasPermission('CAN_SEE_ALL_CLASSES') && hasPermission('CAN_SEE_ALL_SUBJECTS')) {

    } else if (hasPermission('CAN_SEE_CLASS') || hasPermission('CAN_SEE_SUBJECT')) {
        if (isset($userData['role']) && $userData['role'] != 'student')
            $queryData[] = "faculty_id = '" . $userData['id'] . "'";
        else
            $queryData[] = "enrollment_number = '" . $userData['enrollment_number'] . "'";
    }

    if (!empty($classId))
        $queryData[] = "class_id = '$classId'";
    if (!empty($subjectCode))
        $queryData[] = "subject_code = '$subjectCode'";

    $queryData = constructQueryData($queryData);

    if (isset($userData['role']) && $userData['role'] != 'student')
        $query = mysqli_query($con, "
        SELECT 
            teaches_id,
            subject_code,
            subject as subject_name,
            faculty_id as teacher_id,
            class_id,
            department,
            batch,
            semester
        FROM lectures $queryData 
        GROUP BY subject_code, class_id");
    else
        $query = mysqli_query($con, "
        SELECT
            teaches_id,
            subject_code,
            subject as subject_name,
            faculty_id as teacher_id,
            class_id,
            department,
            batch,
            semester 
        FROM student_lectures $queryData 
        GROUP BY subject_code, class_id");


    return $query;
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
                '/\{([a-zA-Z0-9_]+)\}/' => function ($pattern) use ($row) {
                    $key = $pattern[1];

                    if (array_key_exists($key, $row)) {
                        return $row[$key];
                    }

                    return $pattern[0];
                },
            ],
            $str,
        );
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

function idIsRequired()
{
    global $con;

    if (!isset($_REQUEST['id']) || empty($_REQUEST['id'])) {
        http_response_code(403);
        die(json_encode([
            'status' => 'error',
            'message' => 'Id is Required'
        ]));
    }

    return mysqli_real_escape_string($con, trim($_REQUEST['id']));
}
?>