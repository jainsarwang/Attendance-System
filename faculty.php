<?php
require_once "./assets/php/config.php";
?>

<?php
if (strtolower($_SERVER['REQUEST_METHOD']) == 'post' || strtolower($_SERVER['REQUEST_METHOD']) == 'put') {
    header('Content-type: application/json');

    parse_str(file_get_contents('php://input'), $_REQUEST);

    if (
        !isset($_REQUEST['name']) ||
        !isset($_REQUEST['mobile']) ||
        !isset($_REQUEST['username']) ||
        !(strtolower($_SERVER['REQUEST_METHOD']) == 'put' || isset($_REQUEST['password'])) ||
        !isset($_REQUEST['type']) ||
        !isset($_REQUEST['role'])
    ) {
        http_response_code(403);
        die(json_encode([
            'status' => 'error',
            'message' => 'All fields are required'
        ]));
    }

    $id = md5($userData['id'] . time());
    $name = mysqli_real_escape_string($con, trim($_REQUEST['name']));
    $mobile = mysqli_real_escape_string($con, trim($_REQUEST['mobile']));
    $username = mysqli_real_escape_string($con, trim($_REQUEST['username']));
    $password = isset($_REQUEST['password']) ? mysqli_real_escape_string($con, trim($_REQUEST['password'])) : "";
    $type = mysqli_real_escape_string($con, trim($_REQUEST['type']));
    $role = mysqli_real_escape_string($con, trim($_REQUEST['role']));

    if (
        empty($name) ||
        empty($mobile) ||
        empty($username) ||
        (empty($password) && strtolower($_SERVER['REQUEST_METHOD']) == 'post') ||
        empty($type) ||
        empty($role)
    ) {
        http_response_code(403);
        die(json_encode([
            'status' => 'error',
            'message' => 'All fields are required'
        ]));
    }

    if (strlen($name) < 4 || strlen($name) > 50) {
        http_response_code(403);
        die(json_encode([
            'status' => 'error',
            'message' => 'Name Length must be between 4 to 50 characters'
        ]));
    }

    if (strlen($mobile) != 10) {
        http_response_code(403);
        die(json_encode([
            'status' => 'error',
            'message' => 'Mobile number must be of 10 Digits'
        ]));
    } else if (!filter_var($mobile, FILTER_VALIDATE_INT)) {
        http_response_code(403);
        die(json_encode([
            'status' => 'error',
            'message' => 'Invalid mobile number'
        ]));
    }

    if (strlen($username) < 4 || strlen($username) > 20) {
        http_response_code(403);
        die(json_encode([
            'status' => 'error',
            'message' => 'Username Length must be between 4 to 20 characters'
        ]));
    } else if (!preg_match('/^[a-zA-Z0-9\_]+$/', $username)) {
        http_response_code(403);
        die(json_encode([
            'status' => 'error',
            'message' => 'Username can only contains Alphanumeric Character and Underscore'
        ]));
    }
    if (strlen($password) < 8 && strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
        http_response_code(403);
        die(json_encode([
            'status' => 'error',
            'message' => 'Minimum length of password is 8 characters'
        ]));
    } else {
        $password = sha1($password);
    }

    $query = mysqli_query($con, "SELECT * FROM users WHERE user = '$username'");
    if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
        // new Faculty
        if (mysqli_num_rows($query) > 0) {
            http_response_code(401);
            die(json_encode([
                'status' => 'error',
                'message' => 'This Username already exists'
            ]));
        }
        $query = mysqli_query($con, "INSERT INTO users(id, name, mobile, user, password, type, role) VALUES('$id', '$name', '$mobile', '$username', '$password', '$type', '$role')") or die(mysqli_error($con));

        http_response_code(201);
        die(json_encode([
            'status' => 'success',
            'message' => 'Member Added'
        ]));
    } else {
        // updating detail
        if (mysqli_num_rows($query) == 0) {
            http_response_code(401);
            die(json_encode([
                'status' => 'error',
                'message' => 'Invalid Username'
            ]));
        }
        $query = mysqli_query($con, "UPDATE users SET name = '$name', mobile = '$mobile', type = '$type', role = '$role' WHERE user = '$username'");

        die(json_encode([
            'status' => 'success',
            'message' => 'Details Updated Successfully'
        ]));
    }

    die();

} else if (strtolower($_SERVER['REQUEST_METHOD']) == 'delete') {
    parse_str(file_get_contents('php://input'), $_REQUEST);

    if (!isset($_REQUEST['user']) || empty($_REQUEST['user'])) {
        http_response_code(403);
        die(json_encode([
            'status' => 'error',
            'message' => 'Username is Required'
        ]));
    }

    $user = mysqli_real_escape_string($con, trim($_REQUEST['user']));

    $query = mysqli_query($con, "DELETE FROM users WHERE user = '$user'");

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
    <title>Faculty | Attendance System</title>

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
                        <h3>Faculty and Staffs</h3>
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
                        <button class="btn-info" onclick="openAddDialog()">Add New Member</button>
                    </div>

                    <dialog class="add-dialog" tabindex="-1" onclose="closeDialog(event, true)">
                        <div class="close" onclick="this.closest('dialog').close()">&times;</div>
                        <div class="title">New Faculty or Staff Detail</div>

                        <div class="scroll-section center">
                            <div class="form">
                                <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" autocomplete="off">
                                    <div class="form-field">
                                        <input type="text" name="name" id="name" placeholder="Enter Fullname" value=""
                                            required />
                                        <label for="name">Name</label>
                                    </div>

                                    <div class="form-field">
                                        <input type="number" name="mobile" id="mobile"
                                            placeholder="Enter Contact number" value="" required />
                                        <label for="mobile">Contact number</label>
                                    </div>

                                    <div class="form-field">
                                        <input type="text" name="username" id="username" placeholder="Enter Username"
                                            value="" required />
                                        <label for="username">Username</label>
                                    </div>

                                    <div class="form-field">
                                        <input type="password" name="password" id="password"
                                            placeholder="Enter password" value="" required />
                                        <label for="password">Password</label>
                                    </div>

                                    <div class="form-field">
                                        <select name="type" id="type" required>
                                            <option selected disabled value="">Choose type</option>

                                            <option value="permanent">Permanent</option>
                                            <option value="guest">Guest</option>
                                        </select>
                                        <label for="type">Type</label>

                                    </div>

                                    <div class="form-field">
                                        <select name="role" id="role" required>
                                            <option selected disabled value="">Choose role</option>

                                            <option value="faculty">Faculty</option>
                                            <option value="staff">Staff</option>
                                        </select>
                                        <label for="role">Role</label>
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
                        <div class="title">Edit Faculty or Staff Detail</div>

                        <div class="scroll-section center">
                            <div class="form">
                                <form action="<?= $_SERVER['PHP_SELF'] ?>" method="POST" data-method="put"
                                    autocomplete="off">
                                    <div class="form-field">
                                        <input type="text" name="name" id="name" placeholder="Enter Fullname" value=""
                                            required />
                                        <label for="name">Name</label>
                                    </div>

                                    <div class="form-field">
                                        <input type="number" name="mobile" id="mobile"
                                            placeholder="Enter Contact number" value="" required />
                                        <label for="mobile">Contact number</label>
                                    </div>

                                    <div class="form-field">
                                        <input type="text" name="username" id="username" placeholder="Enter Username"
                                            value="" required readonly />
                                        <label for="username">Username</label>
                                    </div>

                                    <div class="form-field">
                                        <select name="type" id="type" required>
                                            <option selected disabled value="">Choose type</option>

                                            <option value="permanent">Permanent</option>
                                            <option value="guest">Guest</option>
                                        </select>
                                        <label for="type">Type</label>

                                    </div>

                                    <div class="form-field">
                                        <select name="role" id="role" required>
                                            <option selected disabled value="">Choose role</option>

                                            <option value="faculty">Faculty</option>
                                            <option value="staff">Staff</option>
                                        </select>
                                        <label for="role">Role</label>
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
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Mobile</th>
                                    <th>Type</th>
                                    <th>Role</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>

                                <?php
                                $query = mysqli_query($con, "SELECT * FROM users WHERE role <> 'hod' ORDER BY name ASC");

                                if (mysqli_num_rows($query) > 0) {
                                    while ($row = mysqli_fetch_array($query)) {
                                        ?>
                                        <tr>
                                            <td class="count"></td>
                                            <td>
                                                <?= $row['name'] ?>
                                            </td>
                                            <td>
                                                <?= $row['user'] ?>

                                            </td>
                                            <td>
                                                <?= $row['mobile'] ?>

                                            </td>
                                            <td>
                                                <?= $row['type'] ?>
                                            </td>
                                            <td>
                                                <?= $row['role'] ?>
                                            </td>
                                            <td class="actions">
                                                <button class="btn-primary" onclick='
                                                    openEditDialog(
                                                        `<?= json_encode([
                                                            'name' => $row['name'],
                                                            'username' => $row['user'],
                                                            'mobile' => $row['mobile'],
                                                            'type' => $row['type'],
                                                            'role' => $row['role'],
                                                        ]) ?>`
                                                    )
                                                '>Edit</button>
                                                <button class="btn-danger" onclick='
                                                    deleteData(
                                                        event,
                                                        `<?= $_SERVER['PHP_SELF'] ?>`,
                                                        `<?= json_encode(['user' => $row['user']]) ?>`,
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