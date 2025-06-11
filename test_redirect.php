<?php
session_start();
$_SESSION['test'] = 'working';
header("Location: admin_dashboard.php");
exit();
