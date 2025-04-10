<?php
session_start();
include('../../config/database.php');

// Kiểm tra kết nối cơ sở dữ liệu
if (!$conn) {
    die("Kết nối cơ sở dữ liệu thất bại: " . mysqli_connect_error());
}

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id'])) {
    die("Bạn cần đăng nhập để truy cập!");
}

$query = "SELECT role FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($user['role'] !== 'admin') {
    die("Chỉ có admin mới có quyền truy cập vào trang này!");
}

// Hàm lấy danh sách nhật ký thao tác
function getAdminLogs() {
    global $conn;
    $query = "SELECT users.username, user_logs.action, user_logs.timestamp, user_logs.ip_address 
              FROM user_logs 
              JOIN users ON user_logs.user_id = users.user_id 
              ORDER BY user_logs.timestamp DESC";
    return mysqli_query($conn, $query);
}

// Hàm tạo báo cáo thống kê nhanh
function getQuickReports() {
    global $conn;
    $query = "SELECT action, COUNT(*) as count 
              FROM user_logs 
              GROUP BY action 
              ORDER BY count DESC";
    return mysqli_query($conn, $query);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhật Ký Thao Tác Và Báo Cáo Cho Admin</title>
    <style>
        h2 {
            text-align: center;
            color: #333;
            font-size: 24px;
            margin-bottom: 20px;
        }
        .section {
            margin-bottom: 40px;
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
            background-color:rgb(0, 1, 2);
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
    </style>
</head>
<body>
    <h2>Nhật Ký Thao Tác Và Báo Cáo Cho Admin</h2>

    <!-- Nhật Ký Thao Tác -->
    <div class="section">
        <h3>Nhật Ký Thao Tác</h3>
        <table>
            <thead>
                <tr>
                    <th>Người Dùng</th>
                    <th>Hành Động</th>
                    <th>Thời Gian</th>
                    <th>Địa Chỉ IP</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = getAdminLogs();
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>
                                <td>" . htmlspecialchars($row['username']) . "</td>
                                <td>" . htmlspecialchars($row['action']) . "</td>
                                <td>" . date("d/m/Y H:i:s", strtotime($row['timestamp'])) . "</td>
                                <td>" . htmlspecialchars($row['ip_address']) . "</td>
                              </tr>";
                    }
                } else {
                    echo "<tr>
                            <td colspan='4'>Không có dữ liệu nhật ký thao tác.</td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Báo Cáo Thống Kê -->
    <div class="section">
        <h3>Báo Cáo Thống Kê Nhanh</h3>
        <table>
            <thead>
                <tr>
                    <th>Hành Động</th>
                    <th>Số Lượng</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = getQuickReports();
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>
                                <td>" . htmlspecialchars($row['action']) . "</td>
                                <td>" . $row['count'] . "</td>
                              </tr>";
                    }
                } else {
                    echo "<tr>
                            <td colspan='2'>Không có dữ liệu thống kê.</td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>