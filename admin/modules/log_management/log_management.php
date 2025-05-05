<?php session_start(); 
include('../../config/database.php'); 

// Xử lý lọc
$where = "1"; 
if (!empty($_GET['user'])) { 
    $user = intval($_GET['user']); 
    $where .= " AND l.user_id = $user"; 
} 
if (!empty($_GET['action'])) { 
    $action = mysqli_real_escape_string($conn, $_GET['action']); 
    $where .= " AND l.action LIKE '%$action%'"; 
} 
if (!empty($_GET['date'])) { 
    $date = mysqli_real_escape_string($conn, $_GET['date']); 
    $where .= " AND DATE(l.created_at) = '$date'"; 
} 

// Lấy danh sách người dùng
$user_query = mysqli_query($conn, "SELECT id, username FROM user");
if (!$user_query) {
    echo "Error in user query: " . mysqli_error($conn);
}

// Truy vấn log - Sắp xếp theo log_id giảm dần
$query = "SELECT l.id, l.user_id, l.action, l.ip_address, l.created_at, u.username FROM user_log l JOIN user u ON l.user_id = u.id WHERE $where ORDER BY l.id DESC"; 
$result = mysqli_query($conn, $query);

// Check if the query executed successfully
if ($result === false) {
    echo "Error in log query: " . mysqli_error($conn);
    $result = null; // Set to null so we can check it later
}
?> 

<div class="p-6"> 
    <!-- Form lọc --> 
    <form method="GET" class="mb-6 flex flex-wrap gap-4 items-end"> 
        <div> 
            <label class="block text-gray-600">Người dùng:</label> 
            <select name="user" class="p-2 border rounded w-48"> 
                <option value="">Tất cả</option> 
                <?php if ($user_query): while ($u = mysqli_fetch_assoc($user_query)): ?> 
                    <option value="<?php echo $u['id']; ?>" <?php if (isset($_GET['user']) && $_GET['user'] == $u['id']) echo 'selected'; ?>> 
                        <?php echo htmlspecialchars($u['name']); ?> 
                    </option> 
                <?php endwhile; endif; ?> 
            </select> 
        </div> 
        
        <div> 
            <label class="block text-gray-600">Hành động:</label> 
            <input type="text" name="action" class="p-2 border rounded w-48" value="<?php echo isset($_GET['action']) ? htmlspecialchars($_GET['action']) : ''; ?>"> 
        </div> 
        
        <div> 
            <label class="block text-gray-600">Ngày:</label> 
            <input type="date" name="date" class="p-2 border rounded" value="<?php echo isset($_GET['date']) ? htmlspecialchars($_GET['date']) : ''; ?>"> 
        </div> 
        
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Lọc</button> 
        <a href="log_management.php" class="ml-2 bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-600">Xóa bộ lọc</a> 
    </form> 
    
    <!-- Bảng log --> 
    <table class="table-auto w-full bg-white shadow rounded-lg"> 
        <thead class="bg-gray-100"> 
            <tr> 
                <th class="px-4 py-2">ID</th> 
                <th class="px-4 py-2">Người thao tác</th> 
                <th class="px-4 py-2">Hành động</th> 
                <th class="px-4 py-2">Địa chỉ IP</th>
                <th class="px-4 py-2">Thời gian</th> 
            </tr> 
        </thead> 
        <tbody> 
            <?php if ($result && mysqli_num_rows($result) > 0): ?> 
                <?php while ($row = mysqli_fetch_assoc($result)) : ?> 
                    <tr class="border-t hover:bg-gray-50"> 
                        <td class="px-4 py-2"><?php echo $row['id']; ?></td>
                        <td class="px-4 py-2"><?php echo htmlspecialchars($row['username']); ?></td>
                        <td class="px-4 py-2"><?php echo htmlspecialchars($row['action']); ?></td>
                        <td class="px-4 py-2"><?php echo htmlspecialchars($row['ip_address']); ?></td>
                        <td class="px-4 py-2"><?php echo $row['created_at']; ?></td>
                    </tr> 
                <?php endwhile; ?> 
            <?php else: ?> 
                <tr><td colspan="5" class="text-center py-4 text-gray-500">Không có kết quả phù hợp.</td></tr>
            <?php endif; ?> 
        </tbody> 
    </table> 
</div>