<?php
// BACKEND: Logout

session_start();
$_SESSION = array();
session_destroy();
header("Location: /index/index.php?logout=success");
exit;
?>