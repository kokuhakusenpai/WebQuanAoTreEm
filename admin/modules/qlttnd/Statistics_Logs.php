<?php
session_start();
include('../../config/database.php');

// Kiểm tra nếu session chưa có thông tin user_id
if (!isset($_SESSION['id'])) {
    header('HTTP/1.1 403 Forbidden');
    die(json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để truy cập!']));
}

// Kiểm tra kết nối cơ sở dữ liệu
if (!$conn) {
    header('HTTP/1.1 500 Internal Server Error');
    die(json_encode(['success' => false, 'message' => 'Kết nối cơ sở dữ liệu thất bại: ' . mysqli_connect_error()]));
}

// Hàm lấy dữ liệu cho các bảng
function getRecentLogs($conn) {
    $query = "SELECT user.username, user_log.action, user_log.timestamp, user_log.ip_address 
              FROM user_log 
              JOIN user ON user_log.id = user.id 
              ORDER BY user_log.timestamp DESC 
              LIMIT 10";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $data;
}

function getOldestLogs($conn) {
    $query = "SELECT user.username, user_log.action, user_log.timestamp, user_log.ip_address 
              FROM user_log 
              JOIN user ON user_log.user_id = user.id 
              ORDER BY user_log.timestamp ASC 
              LIMIT 10";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $data;
}

function getActionStatistics($conn) {
    $query = "SELECT action, COUNT(*) as count 
              FROM user_log
              GROUP BY action 
              ORDER BY count DESC";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $data;
}

// Lấy dữ liệu
$recent_logs = getRecentLogs($conn);
$oldest_logs = getOldestLogs($conn);
$action_stats = getActionStatistics($conn);

// Đóng kết nối
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Thao Tác Người Dùng</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="styles.css" />
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-6xl mx-auto">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Quản Lý Thao Tác Người Dùng</h2>

        <!-- Tab Navigation -->
        <div class="flex border-b border-gray-200 mb-4">
            <button onclick="loadContent('modules/qlttnd/user_activity.php')" class="tab-btn flex items-center px-4 py-2 text-gray-600 hover:text-blue-600 border-b-2 border-transparent hover:border-blue-600 transition-colors">
                <i class="fas fa-user mr-2"></i> Hoạt Động Của Người Dùng
            </button>
            <button onclick="loadContent('modules/qlttnd/role_management.php')" class="tab-btn flex items-center px-4 py-2 text-gray-600 hover:text-blue-600 border-b-2 border-transparent hover:border-blue-600 transition-colors">
                <i class="fas fa-lock mr-2"></i> Quản Lý Quyền Hạn
            </button>
        </div>

        <!-- Tab Content -->
        <div id="tabContent" class="bg-white p-6 rounded-lg shadow-sm">
            <div id="loadingSpinner" class="flex justify-center items-center h-32 hidden">
                <i class="fas fa-spinner fa-spin text-3xl text-blue-500"></i>
            </div>
            <div id="tabContentInner">
                <!-- Admin Log Tabs -->
                <div id="admin_log" class="tab-content active">
                    <div class="flex border-b border-gray-200 mb-4">
                        <button onclick="showSubTab('recent_logs')" class="sub-tab-btn px-4 py-2 text-gray-600 hover:text-blue-600 border-b-2 border-transparent hover:border-blue-600 transition-colors sub-tab-active">Nhật Ký Gần Đây</button>
                        <button onclick="showSubTab('oldest_logs')" class="sub-tab-btn px-4 py-2 text-gray-600 hover:text-blue-600 border-b-2 border-transparent hover:border-blue-600 transition-colors">Nhật Ký Cũ Nhất</button>
                        <button onclick="showSubTab('action_stats')" class="sub-tab-btn px-4 py-2 text-gray-600 hover:text-blue-600 border-b-2 border-transparent hover:border-blue-600 transition-colors">Thống Kê Hành Động</button>
                    </div>

                    <!-- Recent Logs -->
                    <div id="recent_logs" class="sub-tab-content active">
                        <h3 class="text-xl font-semibold text-gray-700 mb-4">Nhật Ký Gần Đây</h3>
                        <div class="table-container">
                            <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="py-2 px-4 border-b text-left text-gray-600">Tên Người Dùng</th>
                                        <th class="py-2 px-4 border-b text-left text-gray-600">Hành Động</th>
                                        <th class="py-2 px-4 border-b text-left text-gray-600">Thời Gian</th>
                                        <th class="py-2 px-4 border-b text-left text-gray-600">Địa Chỉ IP</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($recent_logs): ?>
                                        <?php foreach ($recent_logs as $log): ?>
                                            <tr>
                                                <td class="py-2 px-4 border-b text-gray-700"><?php echo htmlspecialchars($log['username']); ?></td>
                                                <td class="py-2 px-4 border-b text-gray-700"><?php echo htmlspecialchars($log['action']); ?></td>
                                                <td class="py-2 px-4 border-b text-gray-700"><?php echo htmlspecialchars($log['timestamp']); ?></td>
                                                <td class="py-2 px-4 border-b text-gray-700"><?php echo htmlspecialchars($log['ip_address']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="py-2 px-4 border-b text-center text-gray-500">Không có dữ liệu</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Oldest Logs -->
                    <div id="oldest_logs" class="sub-tab-content hidden">
                        <h3 class="text-xl font-semibold text-gray-700 mb-4">Nhật Ký Cũ Nhất</h3>
                        <div class="table-container">
                            <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="py-2 px-4 border-b text-left text-gray-600">Tên Người Dùng</th>
                                        <th class="py-2 px-4 border-b text-left text-gray-600">Hành Động</th>
                                        <th class="py-2 px-4 border-b text-left text-gray-600">Thời Gian</th>
                                        <th class="py-2 px-4 border-b text-left text-gray-600">Địa Chỉ IP</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($oldest_logs): ?>
                                        <?php foreach ($oldest_logs as $log): ?>
                                            <tr>
                                                <td class="py-2 px-4 border-b text-gray-700"><?php echo htmlspecialchars($log['username']); ?></td>
                                                <td class="py-2 px-4 border-b text-gray-700"><?php echo htmlspecialchars($log['action']); ?></td>
                                                <td class="py-2 px-4 border-b text-gray-700"><?php echo htmlspecialchars($log['timestamp']); ?></td>
                                                <td class="py-2 px-4 border-b text-gray-700"><?php echo htmlspecialchars($log['ip_address']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="py-2 px-4 border-b text-center text-gray-500">Không có dữ liệu</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Action Statistics -->
                    <div id="action_stats" class="sub-tab-content hidden">
                        <h3 class="text-xl font-semibold text-gray-700 mb-4">Thống Kê Hành Động</h3>
                        <div class="table-container">
                            <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="py-2 px-4 border-b text-left text-gray-600">Hành Động</th>
                                        <th class="py-2 px-4 border-b text-left text-gray-600">Số Lần</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($action_stats): ?>
                                        <?php foreach ($action_stats as $stat): ?>
                                            <tr>
                                                <td class="py-2 px-4 border-b text-gray-700"><?php echo htmlspecialchars($stat['action']); ?></td>
                                                <td class="py-2 px-4 border-b text-gray-700"><?php echo htmlspecialchars($stat['count']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="2" class="py-2 px-4 border-b text-center text-gray-500">Không có dữ liệu</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentTab = null;

        // Load tab content dynamically
        function loadContent(url) {
            // Update active tab styling
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('tab-active');
            });
            const activeBtn = document.querySelector(`button[onclick="loadContent('${url}')"]`);
            if (activeBtn) {
                activeBtn.classList.add('tab-active');
            }

            // Show loading spinner
            const spinner = document.getElementById('loadingSpinner');
            const tabContentInner = document.getElementById('tabContentInner');
            spinner.classList.remove('hidden');
            tabContentInner.innerHTML = ''; // Clear previous content

            // Fetch tab content
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Không thể tải nội dung tab: ${response.status} - ${response.statusText}`);
                    }
                    return response.text();
                })
                .then(html => {
                    tabContentInner.innerHTML = html;
                    spinner.classList.add('hidden');
                    currentTab = url;
                })
                .catch(error => {
                    spinner.classList.add('hidden');
                    tabContentInner.innerHTML = '<p class="text-red-500">Lỗi khi tải nội dung tab: ' + error.message + '</p>';
                    showToast("Lỗi khi tải nội dung: " + error.message, "error");
                });
        }

        // Show admin log sub-tabs
        function showTab(tabId) {
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('tab-active');
            });
            const activeBtn = document.querySelector(`button[onclick="showTab('${tabId}')"]`);
            if (activeBtn) {
                activeBtn.classList.add('tab-active');
            }

            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
                tab.classList.add('hidden');
            });
            const selectedTab = document.getElementById(tabId);
            if (selectedTab) {
                selectedTab.classList.add('active');
                selectedTab.classList.remove('hidden');
            }
        }

        function showSubTab(subTabId) {
            document.querySelectorAll('.sub-tab-btn').forEach(btn => {
                btn.classList.remove('sub-tab-active');
            });
            const activeSubBtn = document.querySelector(`button[onclick="showSubTab('${subTabId}')"]`);
            if (activeSubBtn) {
                activeSubBtn.classList.add('sub-tab-active');
            }

            document.querySelectorAll('.sub-tab-content').forEach(tab => {
                tab.classList.add('hidden');
            });
            const selectedSubTab = document.getElementById(subTabId);
            if (selectedSubTab) {
                selectedSubTab.classList.remove('hidden');
            }
        }

        // Hiển thị toast notification
        function showToast(message, type) {
            const toast = document.createElement('div');
            toast.className = `toast p-4 rounded-lg text-white ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => toast.classList.add('show'), 100);
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 500);
            }, 3000);
        }

        // Load default sub-tab on page load
        document.addEventListener('DOMContentLoaded', () => {
            showSubTab('recent_logs');
        });
    </script>
</body>
</html>