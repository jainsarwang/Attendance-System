<?php
require_once "./assets/php/config.php";


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


$userClasses = getUserClasses();
$userSubject = getUserSubjects();

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
                        <h3>Dashboard</h3>
                    </div>

                    <div id="filter-options">
                        <div class="filter-form form">
                            <form action="" class="row" data-allow-submit>
                                <div class="form-field">
                                    <select name="class" id="" value="<?= $class_id ?>">
                                        <option value="" selected>All</option>

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
                                        <option value="" selected>All</option>

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
                                        <option value="" selected>All</option>

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

                    <?php

                    if (!empty($class_id)) {
                        ?>
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
                            SELECT a.student_id as enrollment, s.name as student_name,
                                a.semester, a.batch,
                                GROUP_CONCAT(a.month) AS months,
                                GROUP_CONCAT(CONCAT('{', a.subject_code , '}')) AS subject_codes,
                                GROUP_CONCAT(CONCAT('{', a.subject_name , '}')) AS subject_names,
                                GROUP_CONCAT(CONCAT('{', a.presents , '}')) as presents,
                                GROUP_CONCAT(CONCAT('{', a.absents , '}')) as absents,
                                SUM(a.total_presents) as total_presents,
                                SUM(a.total_absents) as total_absents,
                                SUM(a.total_attendance) as total_attendance
                            FROM
                                (SELECT 
                                    a.student_id, a.month,
                                    GROUP_CONCAT(a.subject_code) as subject_code, GROUP_CONCAT(a.subject_name) as subject_name,
                                    a.semester, a.batch,
                                    GROUP_CONCAT(a.present) as presents,
                                    GROUP_CONCAT(a.absent) as absents,
                                    SUM(a.total_presents) as total_presents,
                                    SUM(a.total_absents) as total_absents,
                                    SUM(a.total_attendance) as total_attendance
                                FROM 
                                    (SELECT 
                                        t.class_id,
                                        ar.time,
                                        sub.subject_code AS subject_code, sub.name as subject_name,
                                        c.semester, c.batch,
                                        a.student_id,
                                        SUM(CASE WHEN status = 'P' THEN 1 ELSE 0 END) as present,
                                        SUM(CASE WHEN status = 'A' THEN 1 ELSE 0 END) as absent,
                                        SUM(CASE WHEN status = 'P' THEN 1 ELSE 0 END) as total_presents,
                                        SUM(CASE WHEN status = 'A' THEN 1 ELSE 0 END) as total_absents,
                                        count(status) as total_attendance,
                                        DATE_FORMAT(FROM_UNIXTIME(ar.time), '%m-%y') as month 
                                    FROM 
                                    `attendance_record` ar INNER JOIN `attendances` a INNER JOIN `teaches` t INNER JOIN `classes` c INNER JOIN `subject` sub
                                    ON ar.id = a.record_id AND ar.teaches_id = t.id AND t.class_id = c.id AND t.subject_id = sub.subject_code
                                    $queryData
                                    GROUP BY t.id, month, a.student_id
                                    ORDER BY ar.time, a.student_id
                                ) a 
                                GROUP BY a.month, a.student_id
                            ) a INNER JOIN students s 
                            ON a.student_id = s.enrollment_number
                            GROUP BY a.student_id
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
                                            <th rowspan="3">S. No.</th>
                                            <th rowspan="3">Enrollment Number</th>
                                            <th rowspan="3">Student Name</th>
                                            <?php
                                            if (empty($class_id)) {
                                                echo "<th rowspan='3'>Class</th>";
                                            }
                                            ?>

                                            <?php
                                            $months = explode(',', $attendance_record[0]['months']);
                                            $subject_codes = $attendance_record[0]['subject_codes'];
                                            $subject_names = $attendance_record[0]['subject_names'];

                                            $monthwise_subject_codes = explode('},{', substr($subject_codes, 1, strlen($subject_codes) - 2));
                                            $monthwise_subject_names = explode('},{', substr($subject_names, 1, strlen($subject_names) - 2));

                                            foreach ($months as $idx => $month) {
                                                ?>
                                                <th colspan="<?= 2 * count(explode(',', $monthwise_subject_codes[$idx])) ?>">
                                                    <?= $month ?>
                                                </th>
                                                <?php
                                            }
                                            ?>

                                            <th colspan="3" rowspan="2">Total</th>
                                        </tr>
                                        <tr>
                                            <?php
                                            foreach ($months as $idx => $month) {
                                                $subjects = explode(',', $monthwise_subject_names[$idx]);

                                                foreach ($subjects as $idx => $subject) {
                                                    ?>
                                                    <th colspan="2">
                                                        <?php
                                                        if (!empty($subject_code))
                                                            // echo substr($classTypes[$idx], 0, 3);
                                                            echo '';
                                                        else
                                                            echo $subject;
                                                        ?>
                                                    </th>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </tr>
                                        <tr>
                                            <?php
                                            foreach ($months as $idx => $month) {
                                                $subject_codes = explode(',', $monthwise_subject_codes[$idx]);
                                                echo str_repeat("<th>P</th><th>A</th>", count($subject_codes));
                                            } ?>

                                            <th>P</th>
                                            <th>A</th>
                                            <th>T</th>
                                        </tr>
                                    </thead>
                                    <tbody>

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
                                                $present = $row['presents'];
                                                $absent = $row['absents'];


                                                $monthwise_presents = explode('},{', substr($present, 1, strlen($present) - 2));
                                                $monthwise_absents = explode('},{', substr($absent, 1, strlen($absent) - 2));

                                                foreach ($months as $mIdx => $month) {
                                                    $presents = explode(',', $monthwise_presents[$mIdx]);
                                                    $absents = explode(',', $monthwise_absents[$mIdx]);

                                                    foreach ($presents as $idx => $p) {
                                                        ?>
                                                        <td>
                                                            <?= $presents[$idx] ?>
                                                        </td>
                                                        <td>
                                                            <?= $absents[$idx] ?>
                                                        </td>
                                                        <?php
                                                    }
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
                        <?php
                    } else {
                        echo "<h2 class='error'>Please select a Class</h2>";
                    }
                    ?>

                </div>

            </div>
        </div>
    </div>

    <script src="<?= JS_DIR ?>script.js"></script>
</body>

</html>