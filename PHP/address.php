<?php
session_start();
if (!isset($_SESSION['addresses'])) {
    $_SESSION['addresses'] = [];
}

// Xử lý thêm địa chỉ mới
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["new_address"])) {
    $new_address = htmlspecialchars($_POST["new_address"]);
    $_SESSION['addresses'][] = $new_address;
    echo json_encode(["success" => true, "address" => $new_address]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Địa chỉ giao hàng</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .hidden { display: none; }
        .address-container { margin-top: 10px; }
    </style>
</head>
<body>

<h2>Shipping Address</h2>
<p><b>Tiên Đỗ 0123456789</b></p>

<!-- Hiển thị danh sách địa chỉ -->
<div id="address-list">
    <?php foreach ($_SESSION['addresses'] as $address): ?>
        <p>📍 <?php echo $address; ?></p>
    <?php endforeach; ?>
</div>

<!-- Nút thêm địa chỉ mới -->
<p><a href="#" id="show-form">Thêm địa chỉ mới</a></p>

<!-- Form nhập địa chỉ (ẩn ban đầu) -->
<div id="address-form" class="hidden">
    <input type="text" id="new-address" placeholder="Nhập địa chỉ mới">
    <button id="save-address">Lưu</button>
</div>

<script>
$(document).ready(function() {
    // Hiển thị form khi nhấn "Thêm địa chỉ mới"
    $("#show-form").click(function(e) {
        e.preventDefault();
        $("#address-form").removeClass("hidden");
    });

    // Gửi địa chỉ mới qua AJAX
    $("#save-address").click(function() {
        var address = $("#new-address").val().trim();
        if (address === "") {
            alert("Vui lòng nhập địa chỉ!");
            return;
        }

        $.post("address.php", { new_address: address }, function(response) {
            var data = JSON.parse(response);
            if (data.success) {
                $("#address-list").append("<p>📍 " + data.address + "</p>");
                $("#new-address").val(""); // Xóa nội dung input
                $("#address-form").addClass("hidden"); // Ẩn form
            }
        });
    });
});


function showAddressForm() {
    fetch("address.php")
        .then(response => response.text())
        .then(data => {
            document.getElementById("address-form-container").innerHTML = data;
        })
        .catch(error => console.error("Lỗi tải form:", error));
}
</script>

</body>
</html>
