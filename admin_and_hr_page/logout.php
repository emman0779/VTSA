<?php
session_start();
session_unset();
session_destroy();
header("Location: http://localhost/OJT%20System/index.html");
exit();
?>