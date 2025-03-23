<?php
require_once "./assets/php/config.php";
?>

<?php
if (strtolower($_SERVER['REQUEST_METHOD']) == 'post' || strtolower($_SERVER['REQUEST_METHOD']) == 'put') {
    header('Content-type: application/json');

    parse_str(file_get_contents('php://input'), $_REQUEST);

    if (!isset ($_REQUEST['class']) || !isset ($_REQUEST['subject']) || !isset ($_REQUEST['faculty'])) {
        http_response_code(403);
        die (json_encode([
            'status' => 'error',
            'message' => 'All fields are required'
        ]));
    }

    $id = isset ($_REQUEST['id']) ? $_REQUEST['id'] : '';
    $class = mysqli_real_escape_string($con, trim($_REQUEST['class']));
    $subject = mysqli_real_escape_string($con, trim($_REQUEST['subject']));
    $faculty = mysqli_real_escape_string($con, trim($_REQUEST['faculty']));

    if (empty ($class) || empty ($subject) || empty ($faculty)) {
        http_response_code(403);
        die (json_encode([
            'status' => 'error',
            'message' => 'All fields are required'
        ]));
    }

    $query = mysqli_query($con, "SELECT * FROM teaches WHERE id = '$id' OR ('$id'= '' AND teacher_id = '$faculty' AND class_id = '$class' AND subject_id = '$subject')");
    if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
        // new student detail
        if (mysqli_num_rows($query) > 0) {
            http_response_code(401);
            die (json_encode([
                'status' => 'error',
                'message' => 'This lecture is already assigned to this Faculty'
            ]));
        }

        $id = md5($faculty . $class . $subject);
        $insertValue = "'$id', '$faculty', '$class', '$subject'";

        $query = mysqli_query($con, "INSERT INTO teaches(id, teacher_id, class_id, subject_id) VALUES($insertValue)") or die (mysqli_error($con));

        http_response_code(201);
        die (json_encode([
            'status' => 'success',
            'message' => 'Lecture Added'
        ]));
    } else {
        // upating student detail
        if (mysqli_num_rows($query) == 0) {
            http_response_code(401);
            die (json_encode([
                'status' => 'error',
                'message' => 'Invalid Id'
            ]));
        }
        $updateData = "teacher_id = '$faculty', class_id = '$class', subject_id = '$subject'";

        $query = mysqli_query($con, "UPDATE teaches SET $updateData WHERE id = '$id'");

        die (json_encode([
            'status' => 'success',
            'message' => 'Student Details Updated'
        ]));
    }

} else if (strtolower($_SERVER['REQUEST_METHOD']) == 'delete') {
    parse_str(file_get_contents('php://input'), $_REQUEST);

    if (!isset ($_REQUEST['id']) || empty ($_REQUEST['id'])) {
        http_response_code(403);
        die (json_encode([
            'status' => 'error',
            'message' => 'Id is Required'
        ]));
    }

    $id = mysqli_real_escape_string($con, trim($_REQUEST['id']));

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
    <title>Lectures | Attendance System</title>

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

                    <div id="actions-btn" class="row">
                        <button class="btn-info" onclick="openAddDialog()">Add New Lecture</button>
                    </div>

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
                                    <input type="hidden" name="id">
                                    <div class="form-field">
                                        <select name="faculty" id="faculty" required>
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

                    <div class="table-record">
                        <table>
                            <thead>
                                <tr>
                                    <th>S. No.</th>
                                    <th>Faculty</th>
                                    <th>Class</th>
                                    <th>Subject</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $queryData = [];
                                if (!empty ($faculty_id)) {
                                    $queryData[] = "l.teacher_id = '$faculty_id'";
                                }
                                if (!empty ($class_id)) {
                                    $queryData[] = "l.class_id = '$class_id'";
                                }
                                if (!empty ($subject_code)) {
                                    $queryData[] = "l.subject_id = '$subject_code'";
                                }

                                if(count($queryData) > 0) $queryData = "WHERE " . implode(" AND ", $queryData);
                                else $queryData = '';

                                $query = mysqli_query($con, "
                                    SELECT 
                                        l.id, 
                                        l.teacher_id as faculty_id, t.name as faculty, 
                                        l.class_id, c.batch, c.department, c.semester,
                                        s.subject_code, s.name as subject
                                    FROM 
                                    teaches l INNER JOIN subject s INNER JOIN classes c INNER JOIN users t 
                                    on l.subject_id = s.subject_code AND l.class_id = c.id AND l.teacher_id = t.id
                                    $queryData 
                                    ORDER BY t.name ASC");
                                $count = 1;

                                if (mysqli_num_rows($query) > 0) {
                                    while ($row = mysqli_fetch_array($query)) {
                                        ?>
                                                <tr>
                                                    <td>
                                                        <?= $count++ ?>
                                                    </td>
                                                    <td>
                                                        <?= $row['faculty'] ?>
                                                    </td>
                                                    <td>
                                                        <?= $row['semester'] ?> Semester (Batch
                                                        <?= $row['batch'] ?>)
                                                    </td>
                                                    <td>
                                                        <?= $row['subject'] ?>
                                                        (
                                                        <?= $row['subject_code'] ?> )
                                                    </td>
                                                    <td class="actions">
                                                        <button class="btn-primary" onclick='
                                                    openEditDialog(
                                                        `<?= json_encode([
                                                            'id' => $row['id'],
                                                            'faculty' => $row['faculty_id'],
                                                            'class' => $row['class_id'],
                                                            'subject' => $row['subject_code']
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
                                            <td class="error" colspan="6">No Records Available!!</td>
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

    <script src="<?= JS_DIR ?>script.js"></script>
</body>

</html>