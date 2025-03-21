<nav>
    <li><a href="./">Dashboard</a></li>

    <?php
    if (hasPermission('CAN_SEE_ALL_SUBJECT'))
        echo '<li><a href="subject.php">Subject</a></li>';
    ?>

    <?php
    if (hasPermission('CAN_SEE_ALL_CLASS'))
        echo '<li><a href="class.php">Class</a></li>';
    ?>
    <li><a href="faculty.php">Faculty</a></li>
    <li><a href="lectures.php">Lectures</a></li>
    <li><a href="students.php">Students</a></li>
    <li><a href="attendance.php">Attendances</a></li>
    <?php
    if (hasPermission('CAN_ADD_FEEDBACK') || hasPermission('CAN_SEE_FEEDBACK')) {
        echo '<li><a href="#">Feedback</a></li>';
    }
    ?>
</nav>