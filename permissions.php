<?php
require_once "./assets/php/config.php";
?>

<?php
if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
    header('Content-type: application/json');

    parse_str(file_get_contents('php://input'), $_REQUEST);

    [$role, $permission] = processRequestData('role', 'permission');

    $query = mysqli_query($con, "SELECT id FROM role_has_permission WHERE role = '$role' AND permission = '$permission'");

    // new permission
    if (mysqli_num_rows($query) > 0) {
        http_response_code(401);
        die(json_encode([
            'status' => 'error',
            'message' => 'User already have permission'
        ]));
    }

    $query = mysqli_query($con, "INSERT INTO role_has_permission(role, permission) VALUES('$role', '$permission')") or die(mysqli_error($con));

    http_response_code(201);
    die(json_encode([
        'status' => 'success',
        'message' => 'Permission Assigned'
    ]));

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

    $query = mysqli_query($con, "DELETE FROM role_has_permission WHERE id = '$id'");

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
    <title>Permissions | <?= APP_NAME ?></title>

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
                        <h3>Permissions</h3>
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

                    <?php
                    if (hasPermission(('CAN_EDIT_PERMISSION'))) {
                        ?>
                        <div id="actions-btn" class="row">
                            <button class="btn-info" onclick="openAddDialog()">Assign New Permission</button>
                        </div>
                        <?php
                    }
                    ?>

                    <dialog class="add-dialog" tabindex="-1" onclose="closeDialog(event, true)">
                        <div class="close" onclick="this.closest('dialog').close()">&times;</div>
                        <div class="title">Assign New Permission</div>

                        <div class="scroll-section center">
                            <div class="form">
                                <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" autocomplete="off">
                                    <div class="form-field">
                                        <select name="role" required>
                                            <option selected disabled value="">Select Role</option>
                                            <option value="hod">HOD</option>
                                            <option value="faculty">Faculty</option>
                                            <option value="staff">Staff</option>
                                            <option value="student">Student</option>
                                        </select>
                                        <label for="batch">Role</label>
                                    </div>

                                    <div class="form-field">
                                        <select name="permission" required>
                                            <option selected disabled value="">Select Permission</option>
                                            <?php
                                            $permissions = getAllPermissions();
                                            echo formatQueryToStr('<option value="{id}">{name}</option>', $permissions);
                                            ?>

                                        </select>
                                        <label for="department">Permission</label>
                                    </div>

                                    <div class="form-field">
                                        <button type="submit">Continue</button>
                                    </div>

                                    <div class="msg"></div>
                                </form>
                            </div>
                        </div>
                    </dialog>

                    <?php
                    if (hasPermission('CAN_SEE_ALL_PERMISSIONS')) {
                        ?>

                        <div class="table-record">
                            <table>
                                <thead>
                                    <tr>
                                        <th>S. No.</th>
                                        <th>Role</th>
                                        <th>Permission</th>
                                        <?php
                                        if (hasPermission('CAN_EDIT_PERMISSION'))
                                            echo '<th>Actions</th>';
                                        ?>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php
                                    $query = mysqli_query($con, "SELECT * FROM role_permission_view");

                                    if (mysqli_num_rows($query) > 0) {
                                        while ($row = mysqli_fetch_array($query)) {
                                            ?>
                                            <tr>
                                                <td class="count"></td>
                                                <td>
                                                    <?= $row['role'] ?>
                                                </td>
                                                <td>
                                                    <?= str_replace('_', ' ', $row['permission']) ?>
                                                </td>


                                                <?php
                                                if (hasPermission('CAN_EDIT_PERMISSION')) {
                                                    ?>
                                                    <td class="actions">
                                                        <!-- <button class="btn-primary" onclick='
                                                        openEditDialog(
                                                            `<?= json_encode([
                                                                'id' => $row['id'],
                                                                'role' => $row['role'],
                                                            ]) ?>`
                                                        )
                                                    '>Edit</button> -->

                                                        <button class="btn-danger" onclick='
                                                        deleteData(
                                                            event,
                                                            `<?= $_SERVER['PHP_SELF'] ?>`,
                                                            `<?= json_encode(['id' => $row['id']]) ?>`,
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
                                            <td class="error" colspan="4">No Records Available!!</td>
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

    <script src=" <?= JS_DIR ?>script.js">
    </script>
</body>

</html>