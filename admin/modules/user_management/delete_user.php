<?php
session_start();
include('../../config/database.php'); // Adjust path to database.php file

// Determine if we should return JSON or redirect
$return_json = isset($_GET['json']) && $_GET['json'] == 1;

if ($return_json) {
    header('Content-Type: application/json');
}

// Handle both GET (for backward compatibility) and POST
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Process GET request (keeping old method for compatibility)
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        if ($return_json) {
            echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
        } else {
            echo '<script>
                alert("ID người dùng không hợp lệ");
                window.location.href = "../../index.php";
            </script>';
        }
        exit;
    }
    
    $user_id = intval($_GET['id']);
    
    // Check database connection
    if (!$conn) {
        error_log("Database connection failed: " . mysqli_connect_error());
        if ($return_json) {
            echo json_encode(['success' => false, 'message' => 'Database connection error']);
        } else {
            echo '<script>
                alert("Lỗi kết nối cơ sở dữ liệu");
                window.location.href = "../../index.php";
            </script>';
        }
        exit;
    }
    
    // Delete user
    try {
        // Use prepared statement to prevent SQL injection
        $query = "DELETE FROM user WHERE id = ?";
        $stmt = $conn->prepare($query);
        
        if ($stmt === false) {
            error_log("Prepare failed: " . $conn->error);
            if ($return_json) {
                echo json_encode(['success' => false, 'message' => 'Database query preparation failed']);
            } else {
                echo '<script>
                    alert("Lỗi chuẩn bị truy vấn cơ sở dữ liệu");
                    window.location.href = "../../index.php";
                </script>';
            }
            exit;
        }
        
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                if ($return_json) {
                    echo json_encode(['success' => true, 'message' => 'Xóa người dùng thành công']);
                } else {
                    echo '<script>
                        alert("Xóa người dùng thành công");
                        window.location.href = "../../index.php";
                    </script>';
                }
            } else {
                if ($return_json) {
                    echo json_encode(['success' => false, 'message' => 'Không tìm thấy người dùng']);
                } else {
                    echo '<script>
                        alert("Không tìm thấy người dùng");
                        window.location.href = "../../index.php";
                    </script>';
                }
            }
        } else {
            error_log("Execute failed: " . $stmt->error);
            if ($return_json) {
                echo json_encode(['success' => false, 'message' => 'Lỗi khi xóa người dùng: ' . $stmt->error]);
            } else {
                echo '<script>
                    alert("Lỗi khi xóa người dùng");
                    window.location.href = "../../index.php";
                </script>';
            }
        }
        
        $stmt->close();
    } catch (Exception $e) {
        error_log("Exception when deleting user: " . $e->getMessage());
        if ($return_json) {
            echo json_encode(['success' => false, 'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()]);
        } else {
            echo '<script>
                alert("Đã xảy ra lỗi khi xóa người dùng");
                window.location.href = "../../index.php";
            </script>';
        }
    }
} 
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process POST request (newer, safer method)
    // Check admin permissions
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        if ($return_json) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        } else {
            echo '<script>
                alert("Bạn không có quyền thực hiện hành động này");
                window.location.href = "../../index.php";
            </script>';
        }
        exit;
    }

    // Check CSRF token (uncomment when implemented)
    // if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    //     if ($return_json) {
    //         echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    //     } else {
    //         echo '<script>
    //             alert("Token bảo mật không hợp lệ");
    //             window.location.href = "../index.php";
    //         </script>';
    //     }
    //     exit;
    // }

    // Check user ID
    if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
        if ($return_json) {
            echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
        } else {
            echo '<script>
                alert("ID người dùng không hợp lệ");
                window.location.href = "../../index.php";
            </script>';
        }
        exit;
    }

    $user_id = intval($_POST['id']);

    // Prevent deleting currently logged in account
    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id) {
        if ($return_json) {
            echo json_encode(['success' => false, 'message' => 'Không thể xóa tài khoản đang đăng nhập']);
        } else {
            echo '<script>
                alert("Không thể xóa tài khoản đang đăng nhập");
                window.location.href = "../../index.php";
            </script>';
        }
        exit;
    }

    // Check database connection
    if (!$conn) {
        error_log("Database connection failed: " . mysqli_connect_error());
        if ($return_json) {
            echo json_encode(['success' => false, 'message' => 'Database connection error']);
        } else {
            echo '<script>
                alert("Lỗi kết nối cơ sở dữ liệu");
                window.location.href = "../../index.php";
            </script>';
        }
        exit;
    }

    // Delete user
    try {
        // Use prepared statement to prevent SQL injection
        $query = "DELETE FROM user WHERE id = ?";
        $stmt = $conn->prepare($query);
        
        if ($stmt === false) {
            error_log("Prepare failed: " . $conn->error);
            if ($return_json) {
                echo json_encode(['success' => false, 'message' => 'Database query preparation failed']);
            } else {
                echo '<script>
                    alert("Lỗi chuẩn bị truy vấn cơ sở dữ liệu");
                    window.location.href = "../../index.php";
                </script>';
            }
            exit;
        }
        
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                // Log user deletion activity
                $admin_username = $_SESSION['username'] ?? 'unknown';
                error_log("User ID=$user_id deleted by admin: $admin_username");
                
                if ($return_json) {
                    echo json_encode(['success' => true, 'message' => 'Xóa người dùng thành công']);
                } else {
                    echo '<script>
                        alert("Xóa người dùng thành công");
                        window.location.href = "../../index.php";
                    </script>';
                }
            } else {
                if ($return_json) {
                    echo json_encode(['success' => false, 'message' => 'Không tìm thấy người dùng']);
                } else {
                    echo '<script>
                        alert("Không tìm thấy người dùng");
                        window.location.href = "../../index.php";
                    </script>';
                }
            }
        } else {
            error_log("Execute failed: " . $stmt->error);
            if ($return_json) {
                echo json_encode(['success' => false, 'message' => 'Lỗi khi xóa người dùng: ' . $stmt->error]);
            } else {
                echo '<script>
                    alert("Lỗi khi xóa người dùng");
                    window.location.href = "../../index.php";
                </script>';
            }
        }
        
        $stmt->close();
    } catch (Exception $e) {
        error_log("Exception when deleting user: " . $e->getMessage());
        if ($return_json) {
            echo json_encode(['success' => false, 'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()]);
        } else {
            echo '<script>
                alert("Đã xảy ra lỗi khi xóa người dùng");
                window.location.href = "../../index.php";
            </script>';
        }
    }
} else {
    if ($return_json) {
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    } else {
        echo '<script>
            alert("Phương thức không được cho phép");
            window.location.href = "../../index.php";
        </script>';
    }
}

$conn->close();
?>