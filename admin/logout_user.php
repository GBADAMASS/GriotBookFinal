<?php
session_start();
unset($_SESSION['user_id'], $_SESSION['user_name']);
session_destroy();
header('Location: ../index.php');
exit;
?>
