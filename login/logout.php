<?php

//kullanıcı çıkış yapar
session_start();
$_SESSION = array();
session_destroy();
header("Location: /index/index.php?logout=success");
exit;
?>