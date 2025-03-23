<?php
require_once "./assets/php/config.php";
?>

<?php
if (strtolower($_SERVER['REQUEST_METHOD']) == 'post' || strtolower($_SERVER['REQUEST_METHOD']) == 'put') {
    header('Content-type: application/json');

    parse_str(file_get_contents('php://input'), $_REQUEST);

    if (!isset($_REQUEST['subject_code']) || !isset($_REQUEST['name'])) {
        http_response_code(403);
        die(json_encode([
            'status' => 'error',
            'message' => 'All fields are required'
        ]));
    }

    $subject_code = mysqli_real_escape_string($con, trim($_REQUEST['subject_code']));
    $name = mysqli_real_escape_string($con, trim($_REQUEST['name']));

    if (empty($subject_code) || empty($name)) {
        http_response_code(403);
        die(json_encode([
            'status' => 'error',
            'message' => 'All fields are required'
        ]));
    }

    $query = mysqli_query($con, "SELECT * FROM subject WHERE subject_code = '$subject_code'");
    if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
        // new subject detail
        if (mysqli_num_rows($query) > 0) {
            http_response_code(401);
            die(json_encode([
                'status' => 'error',
                'message' => 'This Subject code already exists'
            ]));
        }
        $query = mysqli_query($con, "INSERT INTO subject(subject_code, name) VALUES('$subject_code', '$name')") or die(mysqli_error($con));

        http_response_code(201);
        die(json_encode([
            'status' => 'success',
            'message' => 'Subject Added'
        ]));
    } else {
        // updating subject detail
        if (mysqli_num_rows($query) == 0) {
            http_response_code(401);
            die(json_encode([
                'status' => 'error',
                'message' => 'Invalid Subject Code'
            ]));
        }
        $query = mysqli_query($con, "UPDATE subject SET name = '$name' WHERE subject_code = '$subject_code'");


        die(json_encode([
            'status' => 'success',
            'message' => 'Subject Details Updated'
        ]));
    }
    die();

} else if (strtolower($_SERVER['REQUEST_METHOD']) == 'delete') {
    parse_str(file_get_contents('php://input'), $_REQUEST);

    if (!isset($_REQUEST['subject_code']) || empty($_REQUEST['subject_code'])) {
        http_response_code(403);
        die(json_encode([
            'status' => 'error',
            'message' => 'Subject Code is Required'
        ]));
    }

    $subject_code = mysqli_real_escape_string($con, trim($_REQUEST['subject_code']));

    $query = mysqli_query($con, "DELETE FROM subject WHERE subject_code = '$subject_code'");

    http_response_code(204);
    die();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subject List | Attendance System</title>

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
                        <h3>Subjects</h3>
                    </div>

                    <div id="filter-options">
                        <div class="filter-form form"></div>

                        <div class="search-form form">
                            <form data-search-record=".table-record tbody tr"
                                data-search-column=".table-record td:nth-child(2), .table-record td:nth-child(3)"
                                data-submit-block>
                                <div class="form-field">
                                    <input type="search" placeholder="Search..." name="search" id="search" />
                                </div>
                            </form>
                        </div>

                    </div>

                    <div id="actions-btn" class="row">
                        <button class="btn-info" onclick="openAddDialog()">Add New Subject</button>
                    </div>

                    <dialog class="add-dialog" tabindex="-1" onclose="closeDialog(event, true)">
                        <div class="close" onclick="this.closest('dialog').close()">&times;</div>
                        <div class="title">New Subject Detail</div>

                        <div class="scroll-section center">
                            <div class="form">
                                <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" autocomplete="off">
                                    <div class="form-field">
                                        <input type="text" name="subject_code" id="subject_code"
                                            placeholder="Enter subject code" value="" required />
                                        <label for="subject_code">Subject Code</label>
                                    </div>

                                    <div class="form-field">
                                        <input type="text" name="name" id="name" placeholder="Enter Subject Name"
                                            value="" required />
                                        <label for="name">Subject Name</label>
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
                        <div class="title">Edit Subject Detail</div>

                        <div class="scroll-section center">
                            <div class="form">
                                <form action="<?= $_SERVER['PHP_SELF'] ?>" method="POST" data-method="put"
                                    autocomplete="off">
                                    <div class="form-field">
                                        <input type="text" name="subject_code" id="subject_code"
                                            placeholder="Enter subject code" value="" readonly required />
                                        <label for="subject_code">Subject Code</label>
                                    </div>

                                    <div class="form-field">
                                        <input type="text" name="name" id="name" placeholder="Enter Subject Name"
                                            value="" required />
                                        <label for="name">Subject Name</label>
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
                                    <th>Subject Code</th>
                                    <th>Subject Name</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>

                                <?php
                                $query = mysqli_query($con, "SELECT * FROM subject ORDER BY subject_code ASC");
                                if (mysqli_num_rows($query) > 0) {
                                    while ($row = mysqli_fetch_array($query)) {
                                        ?>
                                        <tr>
                                            <td class="count"></td>
                                            <td>
                                                <?= $row['subject_code'] ?>
                                            </td>
                                            <td>
                                                <?= $row['name'] ?>

                                            </td>
                                            <td class="actions">
                                                <button class="btn-primary" onclick='
                                                    openEditDialog(
                                                        `<?= json_encode([
                                                            'subject_code' => $row['subject_code'],
                                                            'name' => $row['name']
                                                        ]) ?>`
                                                    )
                                                '>Edit</button>
                                                <button class="btn-danger" onclick='
                                                    deleteData(
                                                        event,
                                                        `<?= $_SERVER['PHP_SELF'] ?>`,
                                                        `<?= json_encode(['subject_code' => $row['subject_code']]) ?>`,
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