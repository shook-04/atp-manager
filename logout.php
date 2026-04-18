<?php
require_once 'includes/db.php'; 
require_once 'includes/auth.php';

$_SESSION = [];
session_destroy();

header("Location: " . BASE_PATH . "/index.php");
exit();
?>
