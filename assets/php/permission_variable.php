<?php

$query = mysqli_query($con, "SELECT * FROM permissions") or die (mysqli_error($con));
while ($row = mysqli_fetch_array($query)) {
    define("CAN_" . strtoupper($row["name"]), strtoupper($row["name"]));
}

// define("CAN_ADD_FACULTY", "ADD_FACULTY");
// define("CAN_SEE_FACULTY", "SEE_FACULTY");
// define("CAN_ADD_STUDENT", "ADD_STUDENT");
// define("CAN_SEE_STUDENT", "SEE_STUDENT");
// define("CAN_ADD_ATTENDANCE", "ADD_ATTENDANCE");
// define("CAN_SEE_ATTENDANCE", "SEE_ATTENDANCE");
?>