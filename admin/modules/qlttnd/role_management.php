<?php
session_start();
include('../../config/database.php');

// Kiểm tra kết nối cơ sở dữ liệu
if (!$conn) {
    die("Kết nối cơ sở dữ liệu thất bại: " . mysqli_connect_error());
}

// Hàm lấy danh sách người dùng
function getUsersWithRoles() {
    global $conn;
    $query = "SELECT user_id, username, email, role FROM users ORDER BY role DESC, username ASC";
    return mysqli_query($conn, $query);
}

// Hàm cập nhật quyền hạn
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['user_id']) && isset($_POST['new_role'])) {
    $user_id = intval($_POST['user_id']);
    $new_role = $_POST['new_role'];

    // Kiểm tra tính hợp lệ của role
    $valid_roles = ['customer', 'admin'];
    if (!in_array($new_role, $valid_roles)) {
        die("Quyền hạn không hợp lệ!");
    }

    $query = "UPDATE users SET role = ? WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $new_role, $user_id);
    if ($stmt->execute()) {
        echo "<script>alert('Quyền hạn đã được cập nhật!');</script>";
        echo "<script>window.location.href = 'role_management.php';</script>";
        exit;
    } else {
        die("Cập nhật quyền hạn thất bại: " . $stmt->error);
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Quyền Hạn</title>
    <style>
        h2 {
            text-align: center;
            color: #333;
            font-size: 22px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        table th {
            background-color: #007bff;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        table tr:hover {
            background-color: #eaf4ff;
        }
        form {
            display: inline-block;
            margin: 0;
        }
    </style>
</head>
<body>
    <h2>Quản Lý Quyền Hạn</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên Người Dùng</th>
                <th>Email</th>
                <th>Quyền Hạn</th>
                <th>Thay Đổi Quyền Hạn</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result = getUsersWithRoles();
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>
                            <td>" . htmlspecialchars($row['user_id']) . "</td>
                            <td>" . htmlspecialchars($row['username']) . "</td>
                            <td>" . htmlspecialchars($row['email']) . "</td>
                            <td>" . htmlspecialchars($row['role']) . "</td>
                            <td>
                                <form method='POST' action=''>
                                    <input type='hidden' name='user_id' value='" . htmlspecialchars($row['user_id']) . "'>
                                    <select name='new_role' required>
                                        <option value='customer'" . ($row['role'] === 'customer' ? ' selected' : '') . ">Customer</option>
                                        <option value='admin'" . ($row['role'] === 'admin' ? ' selected' : '') . ">Admin</option>
                                    </select>
                                    <button type='submit'>Cập Nhật</button>
                                </form>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr>
                        <td colspan='5'>Không có người dùng nào trong hệ thống.</td>
                      </tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>