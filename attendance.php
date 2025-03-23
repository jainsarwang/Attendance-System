<?php
require_once "./assets/php/config.php";
?>

<?php
if (strtolower($_SERVER['REQUEST_METHOD']) == 'post' || strtolower($_SERVER['REQUEST_METHOD']) == 'put') {
    header('Content-type: application/json');

    parse_str(file_get_contents('php://input'), $_REQUEST);

    if (!isset($_REQUEST['teaches_id']) || !isset($_REQUEST['class_type']) || !isset($_REQUEST['date']) || !isset($_REQUEST['student']) || !isset($_REQUEST['attendance'])) {
        http_response_code(403);
        die(json_encode([
            'status' => 'error',
            'message' => 'All fields are required'
        ]));
    }


    $teaches_id = mysqli_real_escape_string($con, trim($_REQUEST['teaches_id']));
    $class_type = mysqli_real_escape_string($con, trim($_REQUEST['class_type']));
    $date = mysqli_real_escape_string($con, trim($_REQUEST['date']));
    $time = strtotime($date . " " . date("H:i:s", time()));
    $students = $_REQUEST['student'];
    $attendances = $_REQUEST['attendance'];
    $id = $_SERVER['REQUEST_METHOD'] == 'put' && isset($_REQUEST['id']) && empty($_REQUEST['id']) ? $_REQUEST['id'] : md5($teaches_id . time());

    if (empty($teaches_id) || empty($class_type) || !$time || !is_array($students) || count($students) == 0 || !is_array($attendances) || count($attendances) == 0 || count($students) != count($attendances)) {
        http_response_code(403);
        die(json_encode([
            'status' => 'error',
            'message' => 'All fields are required'
        ]));
    }

    $query = mysqli_query($con, "SELECT * FROM attendance_record WHERE id = '$id'");
    if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
        // new student detail
        if (mysqli_num_rows($query) > 0) {
            http_response_code(401);
            die(json_encode([
                'status' => 'error',
                'message' => 'This Id already exists'
            ]));
        }

        $insertValue = "'$id', '$teaches_id', '$class_type', '$time', '" . time() . "', '" . time() . "'";
        $query = mysqli_query($con, "INSERT INTO attendance_record(id, teaches_id, class_type, time, created_time, updated_time) VALUES($insertValue)") or die(mysqli_error($con));

        if ($query) {
            $attendance_record_query_data = [];
            for ($i = 0; $i < count($students); $i++) {
                $attendance_record_query_data[] = "('" . md5($id . $students[$i]) . "','" . $students[$i] . "','$id','" . $attendances[$i] . "')";
            }
            $attendance_record_query_data = implode(", ", $attendance_record_query_data);

            $query = mysqli_query($con, "INSERT INTO attendances(id, student_id, record_id, status) VALUES $attendance_record_query_data");
            if ($query) {
                http_response_code(201);
                die(json_encode([
                    'status' => 'success',
                    'message' => 'Attendance Taken'
                ]));
            } else {
                $query = mysqli_query($con, "DELETE FROM attendance_record WHERE id = '$id'");
            }
        }

        http_response_code(500);
        die(json_encode([
            'status' => 'error',
            'message' => 'Unexpected Error Occur'
        ]));

    } else {
        // upating student detail
        if (mysqli_num_rows($query) == 0) {
            http_response_code(401);
            die(json_encode([
                'status' => 'error',
                'message' => 'Invalid Id'
            ]));
        }
        $updateData = "name = '$name', gender = '$gender', mobile = '$mobile'";
        if (!empty($class_id)) {
            $updateData .= ", class_id = '$class_id'";
        }
        $query = mysqli_query($con, "UPDATE students SET $updateData WHERE enrollment_number = '$enrollment'");

        die(json_encode([
            'status' => 'success',
            'message' => 'Student Details Updated'
        ]));
    }

} else if (strtolower($_SERVER['REQUEST_METHOD']) == 'delete') {
    parse_str(file_get_contents('php://input'), $_REQUEST);

    if (!isset($_REQUEST['record_id']) || empty($_REQUEST['record_id'])) {
        http_response_code(403);
        die(json_encode([
            'status' => 'error',
            'message' => 'Record Id is Required'
        ]));
    }

    $record_id = mysqli_real_escape_string($con, trim($_REQUEST['record_id']));

    $query = mysqli_query($con, "START TRANSACTION");
    $query = mysqli_query($con, "
        DELETE FROM attendances WHERE record_id = '$record_id'
    ") or die(mysqli_error($con));
    $query = mysqli_query($con, "DELETE FROM attendance_record WHERE id = '$record_id'");

    if (!$query) {
        http_response_code(403);
        die(json_encode([
            'status' => 'error',
            'message' => 'Unexpected Error Occur'
        ]));
    }
    mysqli_query($con, "COMMIT");

    http_response_code(204);
    die();
}

$class_id = '';
$subject_code = '';
$month = '';
if (isset($_GET['class'])) {
    $class_id = $_GET['class'];
}
if (isset($_GET['subject'])) {
    $subject_code = $_GET['subject'];
}
if (isset($_GET['month'])) {
    $month = $_GET['month'];
}

$userClasses = getMysqlResultToArray(getUserClasses());
$userSubject = getMysqlResultToArray(getUserSubjects());
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

    <link rel="stylesheet" href="<?= CSS_DIR ?>style.css">
</head>

<body>
    <div id="wrapper">
        <?php include "./inc/header.php" ?>

        <div id="main">
            <?php include "./inc/nav.php" ?>

            <div id="main_section">
                <div class="container">

                    <div class="page-title">
                        <h3>Attendances</h3>
                    </div>

                    <div id="filter-options">
                        <div class="filter-form form">
                            <form action="" class="row" data-allow-submit>
                                <div class="form-field">
                                    <select name="class" id="" value="<?= $class_id ?>">
                                        <option value="" selected>All Classes</option>

                                        <?php
                                        foreach ($userClasses as $row) {
                                            ?>
                                            <option value="<?= $row['id'] ?>" <?= $row['id'] == $class_id ? "selected" : "" ?>>
                                                <?= $row['semester'] ?> Sem - Batch
                                                <?= $row['batch'] ?>
                                            </option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="form-field">
                                    <select name="subject" id="">
                                        <option value="" selected>All Subject</option>

                                        <?php
                                        foreach ($userSubject as $row) {
                                            ?>
                                            <option value="<?= $row['subject_code'] ?>"
                                                <?= $row['subject_code'] == $subject_code ? "selected" : "" ?>>
                                                <?= $row['name'] ?> (
                                                <?= $row['subject_code'] ?> )
                                            </option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="form-field">
                                    <select name="month" id="">
                                        <option value="" selected>All Month</option>

                                        <?php
                                        // get oldest time of attendance
                                        $query = mysqli_query($con, "SELECT min(time) as startTime FROM attendance_record");
                                        echo $oldestTime = mysqli_num_rows($query) > 0 ? intval(mysqli_fetch_assoc($query)['startTime']) : 0;
                                        $oldestMonth = date('m', $oldestTime);
                                        $oldestYear = date('Y', $oldestTime);
                                        $currYear = date("Y", time());
                                        $currMonth = date("m", time());

                                        while ($currYear > $oldestYear || ($currYear == $oldestYear && $currMonth >= $oldestMonth)) {
                                            $monthValue = $currMonth . '-' . $currYear;
                                            ?>
                                            <option value="<?= $currMonth . '-' . $currYear ?>" <?= ($currMonth . '-' . $currYear) == $month ? "selected" : "" ?>>
                                                <?= date("M Y", strtotime("01-$currMonth-$currYear")) ?>
                                            </option>
                                            <?php
                                            if ($currMonth == 1) {
                                                $currMonth = 12;
                                                $currYear--;
                                            } else
                                                $currMonth--;
                                        }

                                        ?>
                                    </select>
                                </div>

                                <div class="form-field">
                                    <button type="submit">Load</button>
                                </div>
                            </form>
                        </div>

                        <div class="search-form form">
                            <form data-search-record=".table-record tbody tr"
                                data-search-column=".table-record td:nth-child(2), .table-record td:nth-child(3) .table-record td:nth-child(4) .table-record td:nth-child(5)"
                                data-submit-block>
                                <div class="form-field">
                                    <input type="search" placeholder="Search..." name="search" id="search" />
                                </div>
                            </form>
                        </div>

                    </div>

                    <div id="actions-btn" class="row">
                        <button class="btn-info" onclick="openAddDialog()">Add New Attendance</button>
                    </div>

                    <dialog class="add-dialog" tabindex="-1" onclose="closeDialog(event, true)">
                        <div class="close" onclick="this.closest('dialog').close()">&times;</div>
                        <div class="title">Take Attendance</div>

                        <div class="scroll-section center">
                            <div class="form">
                                <?php

                                if (empty($class_id)) {
                                    ?>
                                    <div class="error">Please Choose Class</div>
                                    <?php
                                } else {
                                    $classSubjectPair = getMysqlResultToArray(getUserClassSubject($class_id));

                                    if (count($classSubjectPair) > 0) {
                                        ?>
                                        <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" autocomplete="off">

                                            <div class="form-field">
                                                <select name="teaches_id" id="teaches" required>
                                                    <option selected disabled value="">Choose Class</option>
                                                    <?php
                                                    foreach ($classSubjectPair as $row) {
                                                        ?>
                                                        <option value="<?= $row['teaches_id'] ?>">
                                                            <?= $row['subject_name'] . "(" . $row['semester'] . " semester)" ?>
                                                        </option>
                                                        <?php
                                                    }
                                                    ?>
                                                </select>

                                                <select name="class_type">
                                                    <option value="" selected disabled>Choose Class Type</option>
                                                    <option value="theory">Theory</option>
                                                    <option value="practical">Practical</option>
                                                </select>
                                            </div>

                                            <div class="form-field">
                                                <input type="date" name="date" id="date" value="<?= date('Y-m-d', time()) ?>"
                                                    required />
                                                <label for="date">Date</label>
                                            </div>

                                            <table id="attendance_recording_table">
                                                <thead>
                                                    <tr>
                                                        <th>S. No.</th>
                                                        <th>Enrollment</th>
                                                        <th>Student Name</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $query = mysqli_query($con, "SELECT * FROM students WHERE students.class_id = '$class_id'");
                                                    if (mysqli_num_rows($query) > 0) {
                                                        while ($row = mysqli_fetch_assoc($query)) {
                                                            ?>
                                                            <tr tabindex="0 ">
                                                                <td class="count"></td>
                                                                <td>
                                                                    <?= $row['enrollment_number'] ?>
                                                                </td>
                                                                <td>
                                                                    <?= $row['name'] ?>
                                                                </td>
                                                                <td>
                                                                    <input type="hidden" name="student[]"
                                                                        value="<?= $row['enrollment_number'] ?>" />
                                                                    <div class="attendance">
                                                                        <input type="checkbox" data-name="attendance[]" tabindex="-1"
                                                                            value="p" data-false-value="a" />
                                                                        <div class=" trigger">
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <?php
                                                        }
                                                    } else {
                                                        ?>
                                                        <td class="error" colspan="4">No Student Available</td>
                                                        <?php
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>

                                            <div class="form-field">
                                                <button type="submit">Continue</button>
                                            </div>

                                            <div class="msg"></div>
                                        </form>

                                    <?php } else {
                                        ?>
                                        <div class="error">You don't have permission to
                                            take attendance</div>
                                        <?php
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </dialog>

                    <dialog class="edit-dialog" tabindex="-1" onclose="closeDialog(event, true)">
                        <div class="close" onclick="this.closest('dialog').close()">&times;</div>
                        <div class="title">Edit Student Detail</div>

                        <div class="scroll-section center">
                            <div class="form">
                                <form action="<?= $_SERVER['PHP_SELF'] ?>" method="POST" data-method="put"
                                    autocomplete="off">
                                    <div class="form-field">
                                        <input type="text" name="enrollment" id="enrollment"
                                            placeholder="Enter enrollment number" value="" readonly required />
                                        <label for="enrollment">Enrollment</label>
                                    </div>

                                    <div class="form-field">
                                        <input type="text" name="name" id="name" placeholder="Enter fullname" value=""
                                            required />
                                        <label for="name">Student Name</label>
                                    </div>
                                    <div class="form-field">
                                        <select name="gender" id="gender" required>
                                            <option selected disabled value="">Choose Gender</option>

                                            <option value="M">Male</option>
                                            <option value="F">Female</option>
                                            <option value="O">Other</option>
                                        </select>
                                        <label for="gender">Gender</label>

                                    </div>

                                    <div class="form-field">
                                        <input type="number" name="mobile" id="mobile" placeholder="Enter mobile number"
                                            value="" required />
                                        <label for="mobile">Mobile</label>
                                    </div>

                                    <div class="form-field">
                                        <select name="class" id="class">
                                            <option selected disabled value="">Choose Class</option>

                                            <?php
                                            $query = mysqli_query($con, "SELECT * FROM classes");
                                            while ($row = mysqli_fetch_array($query)) {
                                                ?>
                                                <option value="<?= $row['id'] ?>">
                                                    <?= $row['semester'] ?> Sem - Batch
                                                    <?= $row['batch'] ?>
                                                </option>
                                                <?php
                                            }
                                            ?>
                                        </select>
                                        <label for="class">Class</label>
                                    </div>

                                    <div class="form-field">
                                        <button type="submit">Update</button>
                                    </div>

                                    <div class="msg"></div>
                                </form>
                            </div>
                        </div>
                    </dialog>

                    <div class="table-record">
                        <?php
                        // load all the records
                        $queryData = [];
                        if (!empty($class_id)) {
                            $queryData[] = "t.class_id = '$class_id'";
                        }
                        if (!empty($subject_code)) {
                            $queryData[] = "t.subject_id = '$subject_code'";
                        }
                        if (!empty($month)) {
                            $startTime = strtotime(date("01-m-Y", strtotime("01-$month")) . " +1 hour");
                            $endTime = strtotime(date("t-m-Y", strtotime("01-$month")) . " +1 hour");
                            $queryData[] = "ar.time BETWEEN $startTime AND $endTime";
                        }
                        $queryData = constructQueryData($queryData);

                        $query = mysqli_query($con, "
                        SELECT 
                            a.student_id as enrollment, s.name as student_name, 
                            a.class_id, c.semester, c.batch, 
                            GROUP_CONCAT(CONCAT('{', a.record_ids, '}')) as records_ids,
                            GROUP_CONCAT(CONCAT('{', a.times, '}')) as times,
                            GROUP_CONCAT(CONCAT('{' , a.subject_code, '}')) as subject_codes,
                            GROUP_CONCAT(CONCAT('{' , a.subject_name, '}')) as subject_names,
                            GROUP_CONCAT(CONCAT('{', a.class_types, '}')) as class_types,
                            GROUP_CONCAT(a.status) as status, 
                            SUM(a.total_presents) as total_presents, SUM(a.total_absents) as total_absents, SUM(a.total_attendance) as total_attendance,
                            GROUP_CONCAT(a.date) as dates
                        FROM
                            (SELECT 
                                a.student_id, 
                                date,
                                GROUP_CONCAT(a.record_id) as record_ids,
                                GROUP_CONCAT(a.class_type) as class_types,
                                a.class_id,
                                GROUP_CONCAT(a.time) as times,
                                GROUP_CONCAT(a.subject_code) as subject_code,
                                GROUP_CONCAT(a.subject_name) as subject_name,
                                GROUP_CONCAT(a.status) as status,
                                SUM(a.total_presents) as total_presents, SUM(a.total_absents) as total_absents, SUM(a.total_attendance) as total_attendance
                            FROM (SELECT 
                                    ar.id as record_id, ar.class_type, t.class_id, ar.time,
                                    GROUP_CONCAT(sub.subject_code) as subject_code, GROUP_CONCAT(sub.name) as subject_name,
                                    a.student_id,  GROUP_CONCAT(a.status) as status,
                                    SUM(CASE WHEN status = 'P' THEN 1 ELSE 0 END) as total_presents,
                                    SUM(CASE WHEN status = 'A' THEN 1 ELSE 0 END) as total_absents,
                                    count(status) as total_attendance,
                                    DATE_FORMAT(FROM_UNIXTIME(ar.time), '%d-%m-%y') as date 
                                FROM 
                                `attendance_record` ar INNER JOIN `attendances` a INNER JOIN `teaches` t INNER JOIN `subject` sub
                                ON ar.id = a.record_id AND ar.teaches_id = t.id AND t.subject_id = sub.subject_code
                                $queryData
                                GROUP BY ar.id, date, a.student_id, t.class_id
                                ORDER BY ar.time, a.student_id) a 
                            GROUP BY date, a.student_id, a.class_id
                            ORDER BY a.time) a INNER JOIN `students` s INNER JOIN classes c
                        ON a.student_id = s.enrollment_number AND a.class_id = c.id
                        GROUP BY a.student_id, a.class_id
                        ORDER BY c.batch 
                        ");

                        $attendance_record = [];
                        while ($row = mysqli_fetch_assoc($query)) {
                            $attendance_record[] = $row;
                        }

                        if (count($attendance_record) > 0) {
                            ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th rowspan="2">S. No.</th>
                                        <th rowspan="2">Enrollment Number</th>
                                        <th rowspan="2">Student Name</th>
                                        <?php
                                        if (empty($class_id)) {
                                            echo "<th rowspan='2'>Class</th>";
                                        }
                                        ?>

                                        <?php
                                        $dates = explode(',', $attendance_record[0]['dates']);
                                        $record_ids = $attendance_record[0]['records_ids'];
                                        $subject_codes = $attendance_record[0]['subject_codes'];
                                        $subject_names = $attendance_record[0]['subject_names'];
                                        $class_types = $attendance_record[0]['class_types'];

                                        $datewise_record_ids = explode('},{', substr($record_ids, 1, strlen($record_ids) - 2));
                                        $datewise_subject_codes = explode('},{', substr($subject_codes, 1, strlen($subject_codes) - 2));
                                        $datewise_subject_names = explode('},{', substr($subject_names, 1, strlen($subject_names) - 2));
                                        $datewise_class_types = explode('},{', substr($class_types, 1, strlen($class_types) - 2));

                                        foreach ($dates as $idx => $date) {
                                            ?>
                                            <th colspan="<?= count(explode(',', $datewise_subject_codes[$idx])) ?>">
                                                <?= $date ?>
                                            </th>
                                            <?php
                                        }
                                        ?>

                                        <th colspan="3">Total</th>
                                    </tr>
                                    <tr>
                                        <?php
                                        foreach ($dates as $idx => $date) {
                                            $records = explode(',', $datewise_record_ids[$idx]);
                                            $subjects = explode(',', $datewise_subject_names[$idx]);
                                            $classTypes = explode(',', $datewise_class_types[$idx]);

                                            foreach ($subjects as $idx => $subject) {
                                                ?>
                                                <th>
                                                    <?php
                                                    if (!empty($subject_code))
                                                        echo substr($classTypes[$idx], 0, 3);
                                                    else
                                                        echo $subject . " (" . substr($classTypes[$idx], 0, 1) . ")";
                                                    ?>
                                                    <div class="hover-btn">
                                                        <button class="btn-primary">Edit</button>
                                                        <button class="btn-danger" onclick='
                                                            deleteData(
                                                                event,
                                                                `<?= $_SERVER['PHP_SELF'] ?>`,
                                                                `<?= json_encode(['record_id' => $records[$idx]]) ?>`,
                                                                ``
                                                            )
                                                        '>Delete</button>
                                                    </div>
                                                </th>
                                                <?php
                                            }
                                        }
                                        ?>

                                        <th>P</th>
                                        <th>A</th>
                                        <th>T</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <!-- attendance_record.id as record_id, attendance_record.class_type, attendance_record.time as attendance_time, CONCAT('{', DATE_FORMAT(FROM_UNIXTIME(time), '%d-%m-%y') ,'}') as date,
                                    attendances.id as attendace_id, GROUP_CONCAT(attendances.status) as status, 
                                    SUM(CASE WHEN attendances.status = 'P' THEN 1 ELSE 0 END) as total_presents, SUM(CASE WHEN attendances.status = 'A' THEN 1 ELSE 0 END) as total_absents, count(attendances.status) as total_attendance,
                                    students.enrollment_number as enrollment, students.name as student_name -->
                                    <?php
                                    foreach ($attendance_record as $row) {
                                        ?>
                                        <tr>
                                            <td class="count"></td>
                                            <td>
                                                <?= $row['enrollment'] ?>
                                            </td>
                                            <td>
                                                <?= $row['student_name'] ?>
                                            </td>

                                            <?php
                                            if (empty($class_id)) {
                                                ?>
                                                <td>
                                                    <?= $row['semester'] . " Semester (Batch " . $row['batch'] . ")" ?>
                                                </td>
                                                <?php
                                            }
                                            ?>

                                            <?php
                                            $status = explode(',', $row['status']);

                                            foreach ($status as $s) {
                                                ?>
                                                <td>
                                                    <?= $s ?>
                                                </td>
                                                <?php
                                            }
                                            ?>
                                            <td>
                                                <?= $row['total_presents'] ?>
                                            </td>
                                            <td>
                                                <?= $row['total_absents'] ?>
                                            </td>
                                            <td>
                                                <?= $row['total_attendance'] ?>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    echo "</pre>";
                                    ?>
                                </tbody>

                            </table>

                            <?php
                        } else {
                            ?>
                            <div class="error">No records Found</div>
                            <?php
                        }
                        ?>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <script src="<?= JS_DIR ?>script.js"></script>
    <script>
        document.querySelectorAll('#attendance_recording_table tbody tr').forEach(tr =>
            tr.addEventListener('keydown', function (e) {
                const checkbox = tr.querySelector('input[type=checkbox]');

                if (e.code == 'KeyA') {
                    checkbox.checked = false;
                } else if (e.code == 'KeyP') {
                    checkbox.checked = true;
                }
            })
        )
    </script>
</body>

</html>