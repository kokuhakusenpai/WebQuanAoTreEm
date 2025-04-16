<?php
session_start();
session_unset();
session_destroy();

// Xóa cookie
setcookie("user_id", "", time() - 3600, "/");
setcookie("username", "", time() - 3600, "/");

header("Location: trangchu.html"); // Chuyển hướng về trang chủ
exit;
?>