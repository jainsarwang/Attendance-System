<nav>
    <li><a href="./">Dashboard</a></li>
    <li><a href="timetable.php">Timetable</a></li>
    <li><a href="student_feedback.php">Student Feedback</a></li>

    <?php
    if (
        hasPermission('CAN_SEE_ALL_SUBJECTS') ||
        hasPermission('CAN_SEE_SUBJECT') ||
        hasPermission('CAN_ADD_SUBJECT') ||
        hasPermission('CAN_EDIT_SUBJECT')
    )
        echo '<li><a href="' . ROOT . 'subject.php">Subject</a></li>';
    ?>

    <?php
    if (
        hasPermission('CAN_SEE_ALL_CLASSES') ||
        hasPermission('CAN_SEE_CLASS') ||
        hasPermission('CAN_ADD_CLASS') ||
        hasPermission('CAN_EDIT_CLASS')
    )
        echo '<li><a href="' . ROOT . 'class.php">Class</a></li>';
    ?>

    <?php
    if (
        hasPermission('CAN_SEE_ALL_MEMBERS') ||
        hasPermission('CAN_SEE_MEMBER') ||
        hasPermission('CAN_ADD_MEMBER') ||
        hasPermission('CAN_EDIT_MEMBER')
    )
        echo '<li><a href="' . ROOT . 'faculty.php">Faculty</a></li>';
    ?>

    <?php
    if (
        hasPermission('CAN_SEE_ALL_LECTURES') ||
        hasPermission('CAN_SEE_LECTURE') ||
        hasPermission('CAN_ADD_LECTURE') ||
        hasPermission('CAN_EDIT_LECTURE')
    )
        echo '<li><a href="' . ROOT . 'lectures.php">Lectures</a></li>';
    ?>

    <?php
    if (
        hasPermission('CAN_SEE_ALL_STUDENTS') ||
        hasPermission('CAN_SEE_STUDENT') ||
        hasPermission('CAN_ADD_STUDENT') ||
        hasPermission('CAN_EDIT_STUDENT')
    )
        echo '<li><a href="' . ROOT . 'students.php">Students</a></li>';
    ?>

    <?php
    if (
        hasPermission('CAN_SEE_ALL_ATTENDANCES') ||
        hasPermission('CAN_SEE_ATTENDANCE') ||
        hasPermission('CAN_ADD_ATTENDANCE') ||
        hasPermission('CAN_EDIT_ATTENDANCE')
    )
        echo '<li><a href="' . ROOT . 'attendance.php">Attendances</a></li>';
    ?>

    <?php
    if (
        hasPermission('CAN_SEE_ALL_FEEDBACKS') ||
        hasPermission('CAN_SEE_FEEDBACK') ||
        hasPermission('CAN_ADD_FEEDBACK')
    ) {
        echo '<li><a href="' . ROOT . '#">Feedback</a></li>';
    }
    ?>

    <?php
    if (
        hasPermission('CAN_SEE_ALL_PERMISSIONS') ||
        hasPermission('CAN_ADD_PERMISSION') ||
        hasPermission('CAN_EDIT_PERMISSION')
    ) {
        echo '<li><a href="' . ROOT . 'permissions.php">Permissions</a></li>';
    }
    ?>
</nav>