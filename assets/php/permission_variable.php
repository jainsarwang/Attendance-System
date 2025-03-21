<?php

// This file is work on adding all permissions as the contant variables throughout the application

$query = mysqli_query($con, "SELECT * FROM permissions") or die(mysqli_error($con));
while ($row = mysqli_fetch_array($query)) {
    // ex: SEE_CLASS => CAN_SEE_CLASS

    define("CAN_" . strtoupper($row["name"]), strtoupper($row["name"]));
}

?>