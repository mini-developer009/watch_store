<?php
require_once '../config.php';
header('Content-Type: application/json');
// --- নিষ্ক্রিয় (Deactivated) অ্যাডমিন চেক ---
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'অনুমতি নেই।']);
    exit;
}
if (!$_SESSION['is_super'] && (!isset($_SESSION['is_active']) || $_SESSION['is_active'] == false)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'আপনার অ্যাকাউন্টটি নিষ্ক্রিয় করা হয়েছে। কোনো পরিবর্তন করার অনুমতি নেই।']);
    exit;
}
// --- চেক শেষ ---

function send_error($message, $code = 400) {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

if (!isset($_SESSION['admin_id'])) send_error('অনুমতি নেই।', 403);

$product_id = intval($_POST['product_id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');

// *** নতুন কোড ***
$discount_price = trim($_POST['price'] ?? ''); // 'price' এখন ছাড় মূল্য
$main_price = trim($_POST['main_price'] ?? ''); // নতুন 'main_price' কলাম

if (empty($product_id) || empty($title) || empty($discount_price) || empty($description)) {
    send_error('সকল ঘর (ছবি বাদে) পূরণ করা আবশ্যক।');
}

// যদি main_price খালি থাকে বা 0 হয়, তবে এটিকে NULL সেট করুন
$main_price_to_db = (!empty($main_price) && is_numeric($main_price) && $main_price > 0) ? $main_price : NULL;

try {
    $stmt = $pdo->prepare("SELECT images FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $current_product = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$current_product) send_error('প্রোডাক্ট খুঁজে পাওয়া যায়নি।', 404);
    
    $image_path_to_db = $current_product['images']; 
    $old_image_path = '../' . $current_product['images'];

    if (isset($_FILES['product_image_edit']) && $_FILES['product_image_edit']['error'] == 0) {
        $file = $_FILES['product_image_edit'];
        $upload_dir = '../products/'; 

        if ($file['size'] > 2 * 1024 * 1024) send_error('ফাইল ২MB-এর বড় হতে পারবে না।');
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->file($file['tmp_name']);
        if (!in_array($mime_type, ['image/jpeg', 'image/png', 'image/gif'])) send_error('শুধুমাত্র JPG, PNG, GIF ফাইল অনুমোদিত।');

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $unique_name = uniqid('prod_') . time() . '.' . $extension;
        $target_file = $upload_dir . $unique_name;

        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            $image_path_to_db = 'products/' . $unique_name;
            if (file_exists($old_image_path) && strpos($old_image_path, 'https://') === false) {
                unlink($old_image_path);
            }
        } else {
            send_error('নতুন ছবি আপলোড করা যায়নি।', 500);
        }
    }

    // *** SQL আপডেট করা হয়েছে ***
    $sql = "UPDATE products SET title = ?, price = ?, main_price = ?, description = ?, images = ? WHERE id = ?";
    $pdo->prepare($sql)->execute([$title, $discount_price, $main_price_to_db, $description, $image_path_to_db, $product_id]);
    
    echo json_encode(['success' => true, 'message' => 'প্রোডাক্ট সফলভাবে আপডেট করা হয়েছে!']);

} catch (PDOException $e) {
    send_error('ডেটাবেস ত্রুটি: ' . $e->getMessage(), 500);
}
?>