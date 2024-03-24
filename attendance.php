<?php
require_once "./assets/php/config.php";
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
                        <h3>Attendance</h3>
                    </div>

                    <div id="filter-options">
                        <div class="filter-form form">
                            <form action="" class="row">
                                <div class="form-field">
                                    <select name="class" id="">
                                        <option value="" selected disabled>Choose Class</option>
                                    </select>
                                </div>

                                <div class="form-field">
                                    <select name="subject" id="">
                                        <option value="" selected disabled>Choose Subject</option>
                                    </select>
                                </div>

                                <div class="form-field">
                                    <select name="month" id="">
                                        <option value="" selected disabled>Choose Month</option>
                                    </select>
                                </div>

                                <div class="form-field">
                                    <button type="submit">Load</button>
                                </div>
                            </form>
                        </div>

                        <div class="search-form form">
                            <form data-search-record="">
                                <div class="form-field">
                                    <input type="search" placeholder="Search..." name="search" id="search" />
                                </div>
                            </form>
                        </div>

                    </div>

                    <div id="actions-btn" class="row">
                        <button class="btn-info" onclick="openEditDialog()">Add New Attendance</button>
                    </div>

                    <dialog class="edit-dialog" tabindex="-1">
                        <div class="close" onclick="closeDialog(event, true)">&times;</div>
                        <div class="title">Attendance</div>
                    </dialog>

                    <!-- Default fetch current month attendance -->
                </div>

            </div>
        </div>
    </div>

    <script src="<?= JS_DIR ?>script.js"></script>
</body>

</html>