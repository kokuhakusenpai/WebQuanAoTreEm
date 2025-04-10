<?php
session_start();
include('../../config/database.php');

// Kiểm tra kết nối cơ sở dữ liệu
if (!$conn) {
    die("Kết nối cơ sở dữ liệu thất bại: " . mysqli_connect_error());
}

// Hàm lấy dữ liệu hoạt động của người dùng
function getUserActivityLogs() {
    global $conn;
    $query = "SELECT users.username, user_logs.action, user_logs.timestamp 
              FROM user_logs 
              JOIN users ON user_logs.user_id = users.user_id 
              ORDER BY user_logs.timestamp DESC";
    return mysqli_query($conn, $query);
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hoạt Động Của Người Dùng</title>
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
    </style>
</head>
<body>
    <h2>Hoạt Động Của Người Dùng</h2>
    <table>
        <thead>
            <tr>
                <th>Người Dùng</th>
                <th>Hành Động</th>
                <th>Thời Gian</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result = getUserActivityLogs();
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>
                            <td>" . htmlspecialchars($row['username']) . "</td>
                            <td>" . htmlspecialchars($row['action']) . "</td>
                            <td>" . date("d/m/Y H:i:s", strtotime($row['timestamp'])) . "</td>
                          </tr>";
                }
            } else {
                echo "<tr>
                        <td colspan='3'>Không có dữ liệu hoạt động của người dùng.</td>
                      </tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>