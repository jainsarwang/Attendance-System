<?php
require_once "./assets/php/config.php";
?>

<?php
if (strtolower($_SERVER['REQUEST_METHOD']) == 'post' || strtolower($_SERVER['REQUEST_METHOD']) == 'put') {
    header('Content-type: application/json');

    parse_str(file_get_contents('php://input'), $_REQUEST);

    if (!isset($_REQUEST['enrollment']) || !isset($_REQUEST['name']) || !isset($_REQUEST['gender']) || !isset($_REQUEST['mobile'])) {
        http_response_code(403);
        die(json_encode([
            'status' => 'error',
            'message' => 'All fields are required'
        ]));
    }

    $enrollment = mysqli_real_escape_string($con, trim($_REQUEST['enrollment']));
    $name = mysqli_real_escape_string($con, trim($_REQUEST['name']));
    $gender = mysqli_real_escape_string($con, trim($_REQUEST['gender']));
    $mobile = mysqli_real_escape_string($con, trim($_REQUEST['mobile']));
    $class_id = isset($_REQUEST['class']) ? mysqli_real_escape_string($con, trim($_REQUEST['class'])) : "";

    if (empty($enrollment) || empty($name) || empty($gender) || empty($mobile)) {
        http_response_code(403);
        die(json_encode([
            'status' => 'error',
            'message' => 'All fields are required'
        ]));
    }

    $query = mysqli_query($con, "SELECT * FROM students WHERE enrollment_number = '$enrollment'");
    if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
        // new student detail
        if (mysqli_num_rows($query) > 0) {
            http_response_code(401);
            die(json_encode([
                'status' => 'error',
                'message' => 'This Enrollment number already exists'
            ]));
        }

        $insertValue = "'$enrollment', '$name', '$gender', '$mobile'";
        if (empty($class_id))
            $insertValue .= ", NULL";
        else
            $insertValue .= ", '$class_id'";

        $query = mysqli_query($con, "INSERT INTO students(enrollment_number, name, gender, mobile, class_id) VALUES($insertValue)") or die(mysqli_error($con));

        http_response_code(201);
        die(json_encode([
            'status' => 'success',
            'message' => 'Student Added'
        ]));
    } else {
        // upating student detail
        if (mysqli_num_rows($query) == 0) {
            http_response_code(401);
            die(json_encode([
                'status' => 'error',
                'message' => 'Invalid Enrollment'
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

    if (!isset($_REQUEST['enrollment']) || empty($_REQUEST['enrollment'])) {
        http_response_code(403);
        die(json_encode([
            'status' => 'error',
            'message' => 'Enrollment is Required'
        ]));
    }

    $enrollment = mysqli_real_escape_string($con, trim($_REQUEST['enrollment']));

    $query = mysqli_query($con, "DELETE FROM students WHERE enrollment_number = '$enrollment'");

    http_response_code(204);
    die();
}

$class_id = '';
if (isset($_GET['class'])) {
    $class_id = $_GET['class'];
}
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
                        <h3>Students</h3>
                    </div>

                    <div id="filter-options">
                        <div class="filter-form form">
                            <form action="" class="row" data-allow-submit>
                                <div class="form-field">
                                    <select name="class" id="" value="<?= $class_id ?>">
                                        <option value="" selected>Choose Class</option>

                                        <?php
                                        $query = mysqli_query($con, "SELECT * FROM classes");
                                        while ($row = mysqli_fetch_array($query)) {
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
                    if (hasPermission(('CAN_ADD_STUDENT'))) {
                        ?>
                        <div id="actions-btn" class="row">
                            <button class="btn-info" onclick="openAddDialog()">Add New Student</button>
                        </div>
                        <?php
                    }
                    ?>

                    <dialog class="add-dialog" tabindex="-1" onclose="closeDialog(event, true)">
                        <div class="close" onclick="this.closest('dialog').close()">&times;</div>
                        <div class="title">New Student Detail</div>

                        <div class="scroll-section center">
                            <div class="form">
                                <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" autocomplete="off">
                                    <div class="form-field">
                                        <input type="text" name="enrollment" id="enrollment"
                                            placeholder="Enter enrollment number" value="" required />
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

                    <?php
                    if (hasPermission('CAN_SEE_ALL_STUDENTS')) {
                        ?>
                        <div class="table-record">
                            <table>
                                <thead>
                                    <tr>
                                        <th>S. No.</th>
                                        <th>Enrollment Number</th>
                                        <th>Student Name</th>
                                        <th>Gender</th>
                                        <th>Mobile</th>
                                        <?php
                                        if (hasPermission('CAN_EDIT_STUDENT')) {
                                            ?>
                                            <th>Actions</th>
                                            <?php
                                        }
                                        ?>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php
                                    $queryData = '';
                                    if (!empty($class_id)) {
                                        $queryData .= "WHERE class_id = '$class_id'";
                                    }
                                    $query = mysqli_query($con, "SELECT * FROM students $queryData ORDER BY enrollment_number ASC");
                                    if (mysqli_num_rows($query) > 0) {
                                        while ($row = mysqli_fetch_array($query)) {
                                            ?>
                                            <tr>
                                                <td class="count"></td>
                                                <td>
                                                    <?= $row['enrollment_number'] ?>
                                                </td>
                                                <td>
                                                    <?= $row['name'] ?>

                                                </td>
                                                <td>
                                                    <?= $row['gender'] ?>

                                                </td>
                                                <td>
                                                    <?= $row['mobile'] ?>

                                                </td>

                                                <?php
                                                if (hasPermission('CAN_EDIT_STUDENT')) {
                                                    ?>
                                                    <td class="actions">
                                                        <button class="btn-primary" onclick='
                                                    openEditDialog(
                                                        `<?= json_encode([
                                                            'enrollment' => $row['enrollment_number'],
                                                            'name' => $row['name'],
                                                            'gender' => $row['gender'],
                                                            'mobile' => $row['mobile'],
                                                            'class' => $row['class_id'],
                                                        ]) ?>`
                                                    )
                                                '>Edit</button>
                                                        <button class="btn-danger" onclick='
                                                    deleteData(
                                                        event,
                                                        `<?= $_SERVER['PHP_SELF'] ?>`,
                                                        `<?= json_encode(['enrollment' => $row['enrollment_number']]) ?>`,
                                                        `tr`
                                                    )
                                                '>Delete</button>

                                                    </td>
                                                    <?php
                                                }
                                                ?>
                                            </tr>
                                            <?php
                                        }
                                    } else {
                                        ?>
                                        <tr>
                                            <td class="error" colspan="6">No Records Available!!</td>
                                        </tr>
                                        <?php
                                    }
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