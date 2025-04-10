<?php
include('../../config/database.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "DELETE FROM orders WHERE order_id = $id";
    if (mysqli_query($conn, $query)) {
        header("Location: orders.php");
    } else {
        echo "Lỗi: " . mysqli_error($conn);
    }
}
?>
