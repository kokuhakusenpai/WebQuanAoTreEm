<?php
session_start();
function checkRole($required_role) {
    if ($_SESSION['role'] !== $required_role) {
        die("Bạn không có quyền truy cập.");
    }
}
?>
