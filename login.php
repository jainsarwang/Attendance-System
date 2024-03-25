<?php
if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
    header('Content-type: application/json');
    parse_str(file_get_contents('php://input'), $_REQUEST);


    if (!isset ($_REQUEST['username']) || !isset ($_REQUEST['password'])) {
        http_response_code(403);
        die (json_encode([
            'status' => 'error',
            'message' => 'All fields are required'
        ]));
    }

    $username = mysqli_real_escape_string($con, trim($_REQUEST['username']));
    $password = mysqli_real_escape_string($con, trim($_REQUEST['password']));

    if (empty ($username) || empty ($password)) {
        http_response_code(403);
        die (json_encode([
            'status' => 'error',
            'message' => 'All fields are required'
        ]));
    }

    $userData = validateCredentials($username, $password);

    if (!$userData) {
        http_response_code(401);
        die (json_encode([
            'status' => 'error',
            'message' => 'Invalid Credentials'
        ]));
    }

    $_SESSION['login_user'] = $userData['user'];

    die (json_encode([
        'status' => 'success',
        'message' => 'Login Successfully',
        'redirect' => ROOT,
        'data' => $userData
    ]));
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login |
        <?= APP_NAME ?>
    </title>
    <link rel="stylesheet" href="<?= CSS_DIR ?>style.css">
</head>

<body>
    <div id="wrapper">
        <div class="container">
            <div id="login-page" class="center">
                <div class="form">
                    <h3>Login</h3>
                    <p>Enter your Credentials to login</p>

                    <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST" autocomplete="off">
                        <div class="form-field">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" placeholder="Enter username" />
                        </div>

                        <div class="form-field">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" placeholder="Enter Password" />
                        </div>

                        <div class="form-field">
                            <button type="submit">Login</button>
                        </div>

                        <div class="msg"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="<?= JS_DIR ?>script.js" defer></script>
</body>

</html>