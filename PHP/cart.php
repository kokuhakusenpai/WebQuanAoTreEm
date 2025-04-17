<?php
session_start();
include 'config.php';

// Thêm sản phẩm vào giỏ hàng
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    
    $query = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $query->execute([$product_id]);
    $product = $query->fetch(PDO::FETCH_ASSOC);
    
    if ($product) {
        $_SESSION['cart'][$product_id] = [
            'name' => $product['name'],
            'price' => $product['price'],
            'image' => $product['image_url'],
            'quantity' => $quantity,
            'total' => $product['price'] * $quantity
        ];
    }
    header("Location: cart.html");
    exit();
}

// Xóa sản phẩm khỏi giỏ hàng
if (isset($_GET['remove'])) {
    $product_id = $_GET['remove'];
    unset($_SESSION['cart'][$product_id]);
    header("Location: cart.html");
    exit();
}

// Xử lý thanh toán
// if (isset($_POST['checkout'])) {
//     $user_id = $_SESSION['user_id'] ?? null;
//     $name = $_POST['name'];
//     $phone = $_POST['phone'];
//     $email = $_POST['email'];
//     $address = $_POST['address'];
//     $city = $_POST['city'];
//     $district = $_POST['district'];
//     $ward = $_POST['ward'];
//     $total_price = array_sum(array_column($_SESSION['cart'], 'total'));

//     $order_query = $conn->prepare("INSERT INTO orders (user_id, total_price) VALUES (?, ?)");
//     $order_query->execute([$user_id, $total_price]);
//     $order_id = $conn->lastInsertId();

//     foreach ($_SESSION['cart'] as $product_id => $item) {
//         $detail_query = $conn->prepare("INSERT INTO order_details (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
//         $detail_query->execute([$order_id, $product_id, $item['quantity'], $item['price']]);
//     }
    
//     $_SESSION['cart'] = [];
//     header("Location: success.html");
//     exit();
// }
?>
