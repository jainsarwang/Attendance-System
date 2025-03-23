<?php
require_once "./assets/php/config.php";
?>

<?php
if (strtolower($_SERVER['REQUEST_METHOD']) == 'post' || strtolower($_SERVER['REQUEST_METHOD']) == 'put') {
    header('Content-type: application/json');

    parse_str(file_get_contents('php://input'), $_REQUEST);

    // getting data to variables
    $teaches_id = isset ($_REQUEST['teaches_id']) ? $_REQUEST['teaches_id'] : '';
    [$class, $subject, $faculty] = processRequestData('class', 'subject','faculty');

    $query = mysqli_query($con, "SELECT * FROM teaches WHERE id = '$teaches_id' OR ('$teaches_id'= '' AND teacher_id = '$faculty' AND class_id = '$class' AND subject_id = '$subject')");
    if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
        // new lecture detail
        if (mysqli_num_rows($query) > 0) {
            http_response_code(401);
            die (json_encode([
                'status' => 'error',
                'message' => 'This lecture is already assigned to this Faculty'
            ]));
        }

        $teaches_id = md5($faculty . $class . $subject);
        $insertValue = "'$teaches_id', '$faculty', '$class', '$subject'";

        $query = mysqli_query($con, "INSERT INTO teaches(id, teacher_id, class_id, subject_id) VALUES($insertValue)") or die (mysqli_error($con));

        http_response_code(201);
        die (json_encode([
            'status' => 'success',
            'message' => 'Lecture Added'
        ]));
    } else {
        // upating lecture detail
        if (mysqli_num_rows($query) == 0) {
            http_response_code(401);
            die (json_encode([
                'status' => 'error',
                'message' => 'Invalid Id'
            ]));
        }

        $updateData = "teacher_id = '$faculty', class_id = '$class', subject_id = '$subject'";
        $query = mysqli_query($con, "UPDATE teaches SET $updateData WHERE id = '$teaches_id'");

        die (json_encode([
            'status' => 'success',
            'message' => 'Lecture Details Updated'
        ]));
    }

} else if (strtolower($_SERVER['REQUEST_METHOD']) == 'delete') {
    parse_str(file_get_contents('php://input'), $_REQUEST);

    $id = idIsRequired();

    $query = mysqli_query($con, "DELETE FROM teaches WHERE id = '$id'");

    http_response_code(204);
    die();
}

$faculty_id = '';
$class_id = '';
$subject_code = '';
if (isset ($_GET['faculty'])) {
    $faculty_id = $_GET['faculty'];
}
if (isset ($_GET['class'])) {
    $class_id = $_GET['class'];
}
if (isset ($_GET['subject'])) {
    $subject_code = $_GET['subject'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lectures | <?= APP_NAME ?></title>

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
                        <h3>Lectures</h3>
                    </div>

                    <div id="filter-options">
                        <div class="filter-form form">
                            <form action="" class="row" data-allow-submit>
                                <div class="form-field">
                                    <select name="faculty">
                                        <option selected value="">Choose faculty</option>

                                        <?php
                                        $query = mysqli_query($con, "SELECT * FROM users WHERE role = 'faculty' OR role = 'hod' ORDER BY name ASC");
                                        
                                        while ($row = mysqli_fetch_array($query)) {
                                            ?>
                                            <option value="<?= $row['id'] ?>" <?= $row['id'] == $faculty_id ? 'selected' : '' ?>
                                                >
                                                    <?= $row['name'] ?>
                                                </option>
                                                <?php
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="form-field">
                                    <select name="class">
                                        <option selected value="">Choose Class</option>

                                        <?php
                                        $query = mysqli_query($con, "SELECT * FROM classes ORDER BY batch ASC");
                                        while ($row = mysqli_fetch_array($query)) {
                                            ?>
                                                <option value="<?= $row['id'] ?>" <?= $row['id'] == $class_id ? 'selected' : '' ?>>
                                                    <?= $row['semester'] ?> Semester (Batch
                                                    <?= $row['batch'] ?>)
                                                </option>
                                                <?php
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="form-field">
                                    <select name="subject">
                                        <option selected value="">Choose Subject</option>

                                        <?php
                                        $query = mysqli_query($con, "SELECT * FROM subject ORDER BY name ASC");
                                        while ($row = mysqli_fetch_array($query)) {
                                            ?>
                                                <option value="<?= $row['subject_code'] ?>" <?= $row['subject_code'] == $subject_code ? 'selected' : '' ?>>
                                                    <?= $row['name'] ?> (
                                                    <?= $row['subject_code'] ?>)
                                                </option>
                                                <?php
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
                    if (hasPermission(('CAN_ADD_LECTURE'))) {
                        ?>
                        <div id="actions-btn" class="row">
                            <button class="btn-info" onclick="openAddDialog()">Add New Lecture</button>
                        </div>
                        <?php
                    }
                    ?>

                    <dialog class="add-dialog" tabindex="-1" onclose="closeDialog(event, true)">
                    <div class="close" onclick="this.closest('dialog').close()">&times;</div>
                        <div class="title">New Lecture Detail</div>

                        <div class="scroll-section center">
                            <div class="form">
                                <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" autocomplete="off">
                                    <div class="form-field">
                                        <select name="faculty" id="faculty" required>
                                            <option selected disabled value="">Choose faculty</option>

                                            <?php
                                            $query = mysqli_query($con, "SELECT * FROM users WHERE role = 'faculty' OR role = 'hod' ORDER BY name ASC");
                                            
                                            echo formatQueryToStr('<option value="{id}">{name}</option>', $query);
                                            ?>
                                        </select>
                                        <label for="faculty">Faculty</label>
                                    </div>

                                    <div class="form-field">
                                        <select name="class" id="class" required>
                                            <option selected disabled value="">Choose Class</option>

                                            <?php
                                            $query = mysqli_query($con, "SELECT * FROM classes ORDER BY batch ASC");
                                            
                                            echo formatQueryToStr(
                                                '<option value="{id}">{semester} Semester (Batch {batch])</option>', 
                                                $query
                                            );
                                            ?>
                                        </select>
                                        <label for="class">Class</label>
                                    </div>

                                    <div class="form-field">
                                        <select name="subject" id="subject" required>
                                            <option selected disabled value="">Choose Subject</option>

                                            <?php
                                            $query = mysqli_query($con, "SELECT * FROM subject ORDER BY name ASC");
                                            
                                            echo formatQueryToStr(
                                                '<option value="{subject_code}">{name} ({subject_code})</option>',
                                                $query
                                            );
                                            ?>
                                        </select>
                                        <label for="subject">Faculty Name</label>
                                    </div>

                                    <div class="form-field">
                                        <button type="submit">Continue</button>
                                    </div>

                                    <div class="msg"></div>
                                </form>
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
                                    <input type="hidden" name="teaches_id">
                                    <div class="form-field">
                                        <select name="faculty" id="faculty" required value="1">
                                            <option selected disabled value="">Choose faculty</option>

                                            <?php
                                            $query = mysqli_query($con, "SELECT * FROM users WHERE role = 'faculty' OR role = 'hod' ORDER BY name ASC");
                                            while ($row = mysqli_fetch_array($query)) {
                                                ?>
                                                    <option value="<?= $row['id'] ?>">
                                                        <?= $row['name'] ?>
                                                    </option>
                                                    <?php
                                            }
                                            ?>
                                        </select>
                                        <label for="faculty">Faculty</label>
                                    </div>

                                    <div class="form-field">
                                        <select name="class" id="class" required>
                                            <option selected disabled value="">Choose Class</option>

                                            <?php
                                            $query = mysqli_query($con, "SELECT * FROM classes ORDER BY batch ASC");
                                            while ($row = mysqli_fetch_array($query)) {
                                                ?>
                                                    <option value="<?= $row['id'] ?>">
                                                        <?= $row['semester'] ?> Semester (Batch
                                                        <?= $row['batch'] ?>)
                                                    </option>
                                                    <?php
                                            }
                                            ?>
                                        </select>
                                        <label for="class">Class</label>
                                    </div>

                                    <div class="form-field">
                                        <select name="subject" id="subject" required>
                                            <option selected disabled value="">Choose Subject</option>

                                            <?php
                                            $query = mysqli_query($con, "SELECT * FROM subject ORDER BY name ASC");
                                            while ($row = mysqli_fetch_array($query)) {
                                                ?>
                                                    <option value="<?= $row['subject_code'] ?>">
                                                        <?= $row['name'] ?> (
                                                        <?= $row['subject_code'] ?>)
                                                    </option>
                                                    <?php
                                            }
                                            ?>
                                        </select>
                                        <label for="subject">Faculty Name</label>
                                    </div>

                                    <div class="form-field">
                                        <button type="submit">Update</button>
                                    </div>

                                    <div class="msg"></div>
                                </form>
                            </div>
                        </div>
                    </dialog>

                    <?php
                    if (hasPermission('CAN_SEE_ALL_LECTURES')) {
                        ?>
                        <div class="table-record">
                            <table>
                                <thead>
                                    <tr>
                                        <th>S. No.</th>
                                        <th>Faculty</th>
                                        <th>Class</th>
                                        <th>Subject</th>
                                        <?php
                                        if (hasPermission('CAN_EDIT_LECTURE')) {
                                            ?>
                                            <th>Actions</th>
                                            <?php
                                            }
                                        ?>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php
                                    $queryData = [];
                                    if (!empty ($faculty_id)) 
                                        $queryData[] = "faculty_id = '$faculty_id'";
                                    
                                    if (!empty ($class_id)) 
                                        $queryData[] = "class_id = '$class_id'";
                                    
                                    if (!empty ($subject_code)) 
                                        $queryData[] = "subject_code = '$subject_code'";
                                    

                                    if(count($queryData) > 0) 
                                        $queryData = "WHERE " . implode(" AND ", $queryData);
                                    else 
                                        $queryData = '';

                                    $format = 
                                        '<tr>' .
                                        '<td class="count"></td>' .
                                        '<td>{faculty}</td>' .
                                        '<td>{semester} Semester (Batch {batch})</td>' .
                                        '<td>{subject} ( {subject_code} )</td>' .
                                        (
                                            hasPermission('CAN_EDIT_LECTURE') ? (
                                                '<td class="actions">
                                                    <button class="btn-primary" onclick=\'
                                                        openEditDialog(
                                                            `' . json_encode([
                                                                'teaches_id' => '{teaches_id}',
                                                                'faculty' => '{faculty_id}',
                                                                'class' => '{class_id}',
                                                                'subject' => '{subject_code}'
                                                            ]) . '`
                                                        )
                                                    \'>Edit</button>

                                                    <button class="btn-danger" onclick=\'
                                                        deleteData(
                                                            event,
                                                            `' . $_SERVER['PHP_SELF'] . '`,
                                                            `' . json_encode(['id' => '{teaches_id}']) . '`,
                                                            `tr`
                                                        )
                                                    \'>Delete</button>
                                                </td>'
                                            ) : ''
                                        ).
                                        '</tr>';

                                    $query = mysqli_query($con, "SELECT * FROM  lectures $queryData ORDER BY faculty ASC");

                                    $records = formatQueryToStr(
                                        $format,
                                        $query
                                    );

                                    if(empty($records)) {
                                        ?>
                                        <tr>
                                            <td class="error" colspan="6">No Records Available!!</td>
                                        </tr>
                                        <?php
                                    }else
                                        echo $records;
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <?php
                    } else {
                        ?>
                        <div class="error">Don't have Permissions to View Records</div>
                        <?php
                    }
                    ?>
                </div>

            </div>
        </div>
    </div>

    <script src="<?= JS_DIR ?>script.js"></script>
</body>

</html>