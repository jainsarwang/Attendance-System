<?php
require_once "./assets/php/config.php";
?>

<?php
if (strtolower($_SERVER['REQUEST_METHOD']) == 'post' || strtolower($_SERVER['REQUEST_METHOD']) == 'put') {
    header('Content-type: application/json');

    parse_str(file_get_contents('php://input'), $_REQUEST);

    if (
        !isset($_REQUEST['batch']) ||
        !isset($_REQUEST['department']) ||
        !isset($_REQUEST['semester'])
    ) {
        http_response_code(403);
        die(json_encode([
            'status' => 'error',
            'message' => 'All fields are required'
        ]));
    }

    if ($_SERVER['REQUEST_METHOD'] == 'put' && !isset($_REQUEST['id']) && !empty($_REQUEST['id'])) {
        http_response_code(403);
        die(json_encode([
            'status' => 'error',
            'message' => 'Id is missing'
        ]));
    }

    $id = isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? $_REQUEST['id'] : '';
    $batch = mysqli_real_escape_string($con, trim($_REQUEST['batch']));
    $department = mysqli_real_escape_string($con, trim($_REQUEST['department']));
    $semester = mysqli_real_escape_string($con, trim($_REQUEST['semester']));

    if (
        empty($batch) ||
        empty($department) ||
        empty($semester)
    ) {
        http_response_code(403);
        die(json_encode([
            'status' => 'error',
            'message' => 'All fields are required'
        ]));
    }

    $query = mysqli_query($con, "SELECT * FROM classes WHERE id = '$id' OR ('$id'= '' AND batch = '$batch' AND department = '$department')");
    if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
        // new Faculty
        if (mysqli_num_rows($query) > 0) {
            http_response_code(401);
            die(json_encode([
                'status' => 'error',
                'message' => 'This class is already Exists'
            ]));
        }

        $id = md5($userData['id'] . "class" . time());
        $query = mysqli_query($con, "INSERT INTO classes(id, batch, department, semester) VALUES('$id', '$batch', '$department', '$semester')") or die(mysqli_error($con));

        http_response_code(201);
        die(json_encode([
            'status' => 'success',
            'message' => 'Class Added'
        ]));
    } else {
        // updating detail
        if (mysqli_num_rows($query) == 0) {
            http_response_code(401);
            die(json_encode([
                'status' => 'error',
                'message' => 'Invalid Class'
            ]));
        }
        $query = mysqli_query($con, "UPDATE classes SET batch = '$batch', department = '$department', semester = '$semester' WHERE id = '$id'");

        die(json_encode([
            'status' => 'success',
            'message' => 'Details Updated Successfully'
        ]));
    }

} else if (strtolower($_SERVER['REQUEST_METHOD']) == 'delete') {
    parse_str(file_get_contents('php://input'), $_REQUEST);

    if (!isset($_REQUEST['id']) || empty($_REQUEST['id'])) {
        http_response_code(403);
        die(json_encode([
            'status' => 'error',
            'message' => 'Id is Required'
        ]));
    }

    $id = mysqli_real_escape_string($con, trim($_REQUEST['id']));

    $query = mysqli_query($con, "DELETE FROM classes WHERE id = '$id'");

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
    <title>Classes | Attendance System</title>

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
                        <h3>Classes</h3>
                    </div>

                    <div id="filter-options">
                        <div class="filter-form form"></div>

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
                        <button class="btn-info" onclick="openAddDialog()">Add New Class</button>
                    </div>

                    <dialog class="add-dialog" tabindex="-1" onclose="closeDialog(event, true)">
                        <div class="close" onclick="this.closest('dialog').close()">&times;</div>
                        <div class="title">New Class Detail</div>

                        <div class="scroll-section center">
                            <div class="form">
                                <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" autocomplete="off">
                                    <div class="form-field">
                                        <input type="number" name="batch" id="batch" placeholder="Enter batch" value=""
                                            required />
                                        <label for="batch">Batch</label>
                                    </div>

                                    <div class="form-field">
                                        <input type="text" name="department" id="department"
                                            placeholder="Enter department" value="" required />
                                        <label for="department">Department</label>
                                    </div>

                                    <div class="form-field">
                                        <input type="number" name="semester" id="semester" placeholder="Enter Semester"
                                            value="" required />
                                        <label for="semester">Semester</label>
                                    </div>

                                    <div class="form-field">
                                        <input type="date" name="sem_start" id="sem_start"
                                            placeholder="Select Starting Date of Semester" value="" required />
                                        <label for="sem_start">Semester Starting</label>
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
                        <div class="title">Edit Class Detail</div>

                        <div class="scroll-section center">
                            <div class="form">
                                <form action="<?= $_SERVER['PHP_SELF'] ?>" method="POST" data-method="put"
                                    autocomplete="off">

                                    <input type="hidden" name="id" value="" />
                                    <div class="form-field">
                                        <input type="number" name="batch" id="batch" placeholder="Enter batch" value=""
                                            required readonly />
                                        <label for="batch">Batch</label>
                                    </div>

                                    <div class="form-field">
                                        <input type="text" name="department" id="department"
                                            placeholder="Enter department" value="" required />
                                        <label for="department">Department</label>
                                    </div>

                                    <div class="form-field">
                                        <input type="number" name="semester" id="semester" placeholder="Enter Semester"
                                            value="" required />
                                        <label for="semester">Semester</label>
                                    </div>


                                    <div class="form-field">
                                        <input type="date" name="sem_start" id="sem_start"
                                            placeholder="Select Starting Date of Semester" value="" required />
                                        <label for="sem_start">Semester Starting</label>
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
                        <table>
                            <thead>
                                <tr>
                                    <th>S. No.</th>
                                    <th>Batch</th>
                                    <th>semester</th>
                                    <th>Total Students</th>
                                    <th>Semester Starting</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>

                                <?php
                                $query = mysqli_query($con, "SELECT c.id, c.batch, c.department, c.semester, count(s.class_id) as total_students, c.sem_start FROM classes c LEFT join students s on c.id = s.class_id GROUP BY c.id ORDER BY batch ASC");
                                $count = 1;
                                if (mysqli_num_rows($query) > 0) {
                                    while ($row = mysqli_fetch_array($query)) {
                                        ?>
                                        <tr>
                                            <td>
                                                <?= $count++ ?>
                                            </td>
                                            <td>
                                                <?= $row['batch'] ?>
                                            </td>
                                            <td>
                                                <?= $row['semester'] ?>
                                            </td>
                                            <td>
                                                <?= $row['total_students'] ?>
                                            </td>
                                            <td>
                                                <?= date('d M Y', strtotime($row['sem_start'])) ?>
                                            </td>


                                            <td class="actions">
                                                <button class="btn-primary" onclick='
                                                    openEditDialog(
                                                        `<?= json_encode([
                                                            'id' => $row['id'],
                                                            'batch' => $row['batch'],
                                                            'department' => $row['department'],
                                                            'semester' => $row['semester'],
                                                            'sem_start' => $row['sem_start']
                                                        ]) ?>`
                                                    )
                                                '>Edit</button>
                                                <button class="btn-danger" onclick='
                                                    deleteData(
                                                        event,
                                                        `<?= $_SERVER['PHP_SELF'] ?>`,
                                                        `<?= json_encode(['id' => $row['id']]) ?>`,
                                                        `tr`
                                                    )
                                                '>Delete</button>

                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <tr>
                                        <td class="error" colspan="7">No Records Available!!</td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </tbody>

                        </table>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <script src=" <?= JS_DIR ?>script.js">
    </script>
</body>

</html>