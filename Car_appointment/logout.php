<?php
require_once 'includes/session.php';
$type = isset($_GET['type']) ? $_GET['type'] : '';
if ($type === 'admin') {
    logoutAdmin();
} elseif ($type === 'user') {
    logoutUser();
} else {
    // Logout both
    logoutUser();
    logoutAdmin();
}
?> 