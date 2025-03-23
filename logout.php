<?php

require_once "assets/php/config.php";

session_destroy();

header("location: " . ROOT);

?>