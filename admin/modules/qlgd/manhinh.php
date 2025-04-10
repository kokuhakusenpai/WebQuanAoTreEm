<?php
include('../../config/database.php');

// Lấy cài đặt từ cơ sở dữ liệu
$query = "SELECT * FROM settings WHERE id = 1";
$result = mysqli_query($conn, $query);
$settings = mysqli_fetch_assoc($result);

// Nếu có POST -> cập nhật cài đặt
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $background_color = $_POST['background_color'];
    $text_color = $_POST['text_color'];
    $font_family = json_encode($_POST['font_family']); // Lưu nhiều font dưới dạng JSON
    $font_size = $_POST['font_size'];

    $update_query = "UPDATE settings SET 
                     background_color = '$background_color',
                     text_color = '$text_color',
                     font_family = '$font_family',
                     font_size = '$font_size'
                     WHERE id = 1";
    mysqli_query($conn, $update_query);
    header("Location: manhinh.php");
    exit;
}
?>


<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Cài đặt Màn hình</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded shadow-md w-full max-w-2xl">
        <h2 class="text-2xl font-bold text-center mb-6">Cài đặt Màn hình</h2>
        <form method="POST" class="space-y-6">
            <!-- Màu nền -->
            <div>
                <label class="block font-semibold mb-2">Màu nền:</label>
                <input type="color" name="background_color" id="bgColor"
                       value="<?= $settings['background_color'] ?? '#ffffff' ?>"
                       class="w-12 h-12 rounded border cursor-pointer">
            </div>

            <!-- Màu chữ -->
            <div>
                <label class="block font-semibold mb-2">Màu chữ:</label>
                <input type="color" name="text_color" id="textColor"
                       value="<?= $settings['text_color'] ?? '#000000' ?>"
                       class="w-12 h-12 rounded border cursor-pointer">
            </div>

            <!-- Font chữ (Chọn nhiều font với Select Multiple) -->
            <div>
                <label class="block font-semibold mb-2">Font chữ:</label>
                <select name="font_family[]" multiple id="fontFamily" class="w-full border rounded p-2">
                    <?php
                    $fonts = ['Arial', 'Verdana', 'Tahoma', 'Georgia', 'Courier New', 'Poppins', 'Lato', 'Montserrat', 'Roboto', 'Nunito', 'Open Sans'];
                    $selected_fonts = json_decode($settings['font_family'] ?? '["Arial"]');

                    foreach ($fonts as $font) {
                        $selected = in_array($font, $selected_fonts) ? 'selected' : '';
                        echo "<option value='$font' $selected style='font-family: $font;'>$font</option>";
                    }
                    ?>
                </select>
                <p class="text-sm text-gray-500 mt-2">Nhấn giữ <strong>Ctrl</strong> (hoặc <strong>Cmd</strong> trên Mac) để chọn nhiều font.</p>
            </div>

            <!-- Kích thước font -->
            <div>
                <label class="block font-semibold mb-2 text-lg">Kích thước font:</label>
                <div class="flex items-center gap-4">
                    <input type="range" name="font_size_slider" id="fontSizeSlider" min="12" max="48"
                    value="<?= $settings['font_size'] ?? 16 ?>"
                    class="w-full cursor-pointer">
                    <span id="fontSizeValue" class="text-lg font-bold"><?= $settings['font_size'] ?? 16 ?> px</span>
                </div>
            </div>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">
                Lưu cài đặt
            </button>
        </form>

        <!-- Xem trước -->
        <div class="mt-8 p-6 rounded border text-center" id="previewBox"
             style="background-color: <?= $settings['background_color'] ?? '#ffffff' ?>;
                    color: <?= $settings['text_color'] ?? '#000000' ?>;
                    font-family: <?= implode(", ", $selected_fonts) ?>;
                    font-size: <?= $settings['font_size'] ?? 16 ?>px;">
            <p>Xem trước với các font đã chọn.</p>
        </div>
    </div>
</body>

<script>
    // Cập nhật màu nền khi người dùng chọn
    document.getElementById('bgColor').addEventListener('input', function() {
        document.getElementById('previewBox').style.backgroundColor = this.value;
    });

    // Cập nhật màu chữ khi người dùng chọn
    document.getElementById('textColor').addEventListener('input', function() {
        document.getElementById('previewBox').style.color = this.value;
    });

    // Cập nhật font chữ khi người dùng chọn
    document.getElementById('fontFamily').addEventListener('change', function() {
        const selectedOptions = Array.from(this.selectedOptions).map(option => option.value).join(', ');
        document.getElementById('previewBox').style.fontFamily = selectedOptions;
    });
    
    const fontSizeSlider = document.getElementById('fontSizeSlider');
    const fontSizeValue = document.getElementById('fontSizeValue');
    const previewBox = document.getElementById('previewBox');

    fontSizeSlider.addEventListener('input', function () {
        fontSizeValue.textContent = `${this.value} px`;
        previewBox.style.fontSize = `${this.value}px`;
    });

</script>
</html>


        

