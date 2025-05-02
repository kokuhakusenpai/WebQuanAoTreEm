<?php
session_start();
include('../../config/database.php');

// Kiểm tra kết nối cơ sở dữ liệu
if (!$conn) {
    header('HTTP/1.1 500 Internal Server Error');
    die(json_encode(['success' => false, 'message' => 'Kết nối cơ sở dữ liệu thất bại: ' . mysqli_connect_error()]));
}

// Kiểm tra nếu session chưa có thông tin user_id
if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 403 Forbidden');
    header('Content-Type: application/json');
    die(json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để truy cập!']));
}

// Kiểm tra quyền admin
$query = "SELECT role FROM user WHERE id = ?";
$stmt = $conn->prepare($query);
if ($stmt === false) {
    error_log("Prepare failed: " . $conn->error);
    header('HTTP/1.1 500 Internal Server Error');
    header('Content-Type: application/json');
    die(json_encode(['success' => false, 'message' => 'Lỗi hệ thống khi kiểm tra quyền hạn.']));
}
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user || $user['role'] !== 'admin') {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        // AJAX request
        header('HTTP/1.1 403 Forbidden');
        header('Content-Type: application/json');
        die(json_encode(['success' => false, 'message' => 'Chỉ có admin mới có quyền truy cập vào trang này!']));
    } else {
        // Regular request
        echo '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <p class="font-bold">Lỗi truy cập</p>
            <p>Chỉ có admin mới có quyền truy cập vào trang này!</p>
            </div>';
        exit;
    }
}

// Hàm lấy danh sách thao tác người dùng
function getUserLogs($conn, $limit = 100, $offset = 0, $search = '') {
    $query = "SELECT ul.id, ul.user_id, u.username, ul.action, ul.created_at, ul.ip_address 
              FROM user_log ul
              LEFT JOIN user u ON ul.user_id = u.id
              WHERE 1=1";
    
    $params = [];
    $types = "";
    
    if (!empty($search)) {
        $query .= " AND (u.username LIKE ? OR ul.action LIKE ? OR ul.ip_address LIKE ?)";
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $types .= "sss";
    }
    
    $query .= " ORDER BY ul.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";
    
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $data;
}

// Lấy tổng số bản ghi để phân trang
function getTotalLogs($conn, $search = '') {
    $query = "SELECT COUNT(*) as total 
              FROM user_log ul
              LEFT JOIN user u ON ul.user_id = u.id
              WHERE 1=1";
    
    $params = [];
    $types = "";
    
    if (!empty($search)) {
        $query .= " AND (u.username LIKE ? OR ul.action LIKE ? OR ul.ip_address LIKE ?)";
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $types .= "sss";
    }
    
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        error_log("Prepare failed: " . $conn->error);
        return 0;
    }
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row['total'];
}

// Xử lý tham số tìm kiếm và phân trang
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 15; // Số bản ghi trên mỗi trang
$offset = ($page - 1) * $perPage;

// Kiểm tra nếu là AJAX request để xuất dữ liệu JSON
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    $logs = getUserLogs($conn, $perPage, $offset, $search);
    $totalLogs = getTotalLogs($conn, $search);
    $totalPages = ceil($totalLogs / $perPage);
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => $logs,
        'pagination' => [
            'current' => $page,
            'total' => $totalPages,
            'perPage' => $perPage,
            'totalRecords' => $totalLogs
        ]
    ]);
    exit;
}

// Lấy dữ liệu cho hiển thị ban đầu
$logs = getUserLogs($conn, $perPage, $offset, $search);
$totalLogs = getTotalLogs($conn, $search);
$totalPages = ceil($totalLogs / $perPage);

// Đóng kết nối
$conn->close();
?>

<!-- Nội dung trang quản lý thao tác người dùng -->
<div class="space-y-4">
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-4">
        <h3 class="text-xl font-semibold text-gray-800">Thao Tác Của Người Dùng</h3>
        
        <div class="relative w-full md:w-64">
            <input 
                type="text" 
                id="searchInput" 
                placeholder="Tìm kiếm..."
                value="<?php echo htmlspecialchars($search); ?>"
                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
            <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                <i class="fas fa-search text-gray-400"></i>
            </div>
        </div>
    </div>

    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
        <table class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-white uppercase bg-indigo-600">
                <tr>
                    <th scope="col" class="px-6 py-3">ID</th>
                    <th scope="col" class="px-6 py-3">Người dùng</th>
                    <th scope="col" class="px-6 py-3">Hành động</th>
                    <th scope="col" class="px-6 py-3">Thời gian</th>
                    <th scope="col" class="px-6 py-3">Địa chỉ IP</th>
                </tr>
            </thead>
            <tbody id="logsTableBody">
                <?php if ($logs && count($logs) > 0): ?>
                    <?php foreach ($logs as $log): ?>
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-6 py-4"><?php echo htmlspecialchars($log['id']); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($log['username'] ?? 'Unknown'); ?> (ID: <?php echo htmlspecialchars($log['user_id']); ?>)</td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($log['action']); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($log['created_at']); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($log['ip_address']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr class="bg-white border-b">
                        <td colspan="5" class="px-6 py-4 text-center">Không có dữ liệu</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Phân trang -->
    <?php if ($totalPages > 1): ?>
        <div class="flex justify-between items-center mt-4">
            <div class="text-sm text-gray-600">
                Hiển thị <?php echo min(($page - 1) * $perPage + 1, $totalLogs); ?> - <?php echo min($page * $perPage, $totalLogs); ?> của <?php echo $totalLogs; ?> bản ghi
            </div>
            <div class="flex justify-center">
                <nav class="inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                    <!-- Nút Previous -->
                    <a href="#" 
                       class="<?php echo $page <= 1 ? 'opacity-50 cursor-not-allowed' : ''; ?> pagination-link relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50" 
                       data-page="<?php echo max(1, $page - 1); ?>">
                        <span class="sr-only">Previous</span>
                        <i class="fas fa-chevron-left text-xs"></i>
                    </a>
                    
                    <!-- Các số trang -->
                    <?php
                    $startPage = max(1, min($page - 2, $totalPages - 4));
                    $endPage = min($startPage + 4, $totalPages);
                    
                    for ($i = $startPage; $i <= $endPage; $i++):
                    ?>
                        <a href="#" 
                           class="pagination-link relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium <?php echo $i === $page ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:bg-gray-50'; ?>" 
                           data-page="<?php echo $i; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <!-- Nút Next -->
                    <a href="#" 
                       class="<?php echo $page >= $totalPages ? 'opacity-50 cursor-not-allowed' : ''; ?> pagination-link relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50" 
                       data-page="<?php echo min($totalPages, $page + 1); ?>">
                        <span class="sr-only">Next</span>
                        <i class="fas fa-chevron-right text-xs"></i>
                    </a>
                </nav>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const logsTableBody = document.getElementById('logsTableBody');
        let searchTimeout;
        let currentPage = <?php echo $page; ?>;
        
        // Xử lý sự kiện tìm kiếm
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                currentPage = 1; // Reset về trang 1 khi tìm kiếm
                loadLogs();
            }, 500);
        });
        
        // Xử lý sự kiện click vào nút phân trang
        document.querySelectorAll('.pagination-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const pageNum = parseInt(this.getAttribute('data-page'));
                if (pageNum !== currentPage) {
                    currentPage = pageNum;
                    loadLogs();
                }
            });
        });
        
        // Hàm tải dữ liệu logs
        function loadLogs() {
            // Hiển thị loading
            logsTableBody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center"><i class="fas fa-spinner fa-spin mr-2"></i>Đang tải dữ liệu...</td></tr>';
            
            const searchTerm = searchInput.value.trim();
            
            fetch(`?search=${encodeURIComponent(searchTerm)}&page=${currentPage}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    updateTable(data.data);
                    updatePagination(data.pagination);
                } else {
                    throw new Error(data.message || 'Unknown error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                logsTableBody.innerHTML = `<tr><td colspan="5" class="px-6 py-4 text-center text-red-500">Lỗi: ${error.message}</td></tr>`;
            });
        }
        
        // Cập nhật bảng dữ liệu
        function updateTable(logs) {
            if (logs.length === 0) {
                logsTableBody.innerHTML = '<tr class="bg-white border-b"><td colspan="5" class="px-6 py-4 text-center">Không có dữ liệu</td></tr>';
                return;
            }
            
            let html = '';
            logs.forEach(log => {
                html += `
                <tr class="bg-white border-b hover:bg-gray-50">
                    <td class="px-6 py-4">${escapeHtml(log.id)}</td>
                    <td class="px-6 py-4">${escapeHtml(log.username || 'Unknown')} (ID: ${escapeHtml(log.user_id)})</td>
                    <td class="px-6 py-4">${escapeHtml(log.action)}</td>
                    <td class="px-6 py-4">${escapeHtml(log.created_at)}</td>
                    <td class="px-6 py-4">${escapeHtml(log.ip_address)}</td>
                </tr>`;
            });
            
            logsTableBody.innerHTML = html;
        }
        
        // Cập nhật phân trang
        function updatePagination(pagination) {
            const paginationContainer = document.querySelector('nav[aria-label="Pagination"]');
            if (!paginationContainer) return;
            
            if (pagination.total <= 1) {
                paginationContainer.parentElement.parentElement.style.display = 'none';
                return;
            }
            
            paginationContainer.parentElement.parentElement.style.display = 'flex';
            
            let paginationHtml = '';
            
            // Nút Previous
            paginationHtml += `
            <a href="#" 
               class="${pagination.current <= 1 ? 'opacity-50 cursor-not-allowed' : ''} pagination-link relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50" 
               data-page="${Math.max(1, pagination.current - 1)}">
                <span class="sr-only">Previous</span>
                <i class="fas fa-chevron-left text-xs"></i>
            </a>`;
            
            // Các số trang
            const startPage = Math.max(1, Math.min(pagination.current - 2, pagination.total - 4));
            const endPage = Math.min(startPage + 4, pagination.total);
            
            for (let i = startPage; i <= endPage; i++) {
                paginationHtml += `
                <a href="#" 
                   class="pagination-link relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium ${i === pagination.current ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:bg-gray-50'}" 
                   data-page="${i}">
                    ${i}
                </a>`;
            }
            
            // Nút Next
            paginationHtml += `
            <a href="#" 
               class="${pagination.current >= pagination.total ? 'opacity-50 cursor-not-allowed' : ''} pagination-link relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50" 
               data-page="${Math.min(pagination.total, pagination.current + 1)}">
                <span class="sr-only">Next</span>
                <i class="fas fa-chevron-right text-xs"></i>
            </a>`;
            
            paginationContainer.innerHTML = paginationHtml;
            
            // Cập nhật thông tin hiển thị
            const infoText = document.querySelector('.text-sm.text-gray-600');
            if (infoText) {
                const start = Math.min((pagination.current - 1) * pagination.perPage + 1, pagination.totalRecords);
                const end = Math.min(pagination.current * pagination.perPage, pagination.totalRecords);
                infoText.textContent = `Hiển thị ${start} - ${end} của ${pagination.totalRecords} bản ghi`;
            }
            
            // Thêm lại sự kiện click cho các nút phân trang mới
            document.querySelectorAll('.pagination-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const pageNum = parseInt(this.getAttribute('data-page'));
                    if (pageNum !== currentPage) {
                        currentPage = pageNum;
                        loadLogs();
                    }
                });
            });
        }
        
        // Helper function to escape HTML
        function escapeHtml(text) {
            if (text === null || text === undefined) return '';
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
        }
    });
</script>