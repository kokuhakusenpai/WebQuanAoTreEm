<?php 
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';

    $api_url = "http://localhost/api/signup"; // Gọi API Java
    $data = json_encode(["name" => $name, "phone" => $phone, "password" => $password]);

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    if ($result["success"]) {
        // ✅ Lưu tài khoản vào `users.json`
        $file = 'users.json';
        $users = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

        // ✅ Kiểm tra xem tài khoản mặc định đã tồn tại chưa
        $hasAdmin = false;
        foreach ($users as $user) {
            if ($user['phone'] === 'admin01') {
                $hasAdmin = true;
                break;
            }
        }

        // ✅ Nếu chưa có, thêm tài khoản mặc định
        if (!$hasAdmin) {
            $users[] = [
                "name" => "Nguyễn Thị Thu Cúc",
                "phone" => "admin01",
                "password" => password_hash("Cuc0903@", PASSWORD_DEFAULT)
            ];
        }

        // ✅ Thêm tài khoản mới đăng ký
        $users[] = [
            "name" => $name,
            "phone" => $phone,
            "password" => password_hash($password, PASSWORD_DEFAULT)
        ];

        // ✅ Lưu vào file JSON
        file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT));

        // ✅ Chuyển hướng sau khi đăng ký thành công
        header("Location: trangchu.html");
        exit();
    } else {
        echo "<script>alert('Đăng ký thất bại: " . $result["message"] . "'); window.history.back();</script>";
    }
}
?>
