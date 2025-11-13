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

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');

// *** নতুন কোড ***
$discount_price = trim($_POST['price'] ?? ''); // 'price' এখন ছাড় মূল্য
$main_price = trim($_POST['main_price'] ?? ''); // নতুন 'main_price' কলাম

if (empty($title) || empty($discount_price) || empty($description)) send_error('শিরোনাম, ছাড় মূল্য, এবং বিবরণ আবশ্যক।');
if (!is_numeric($discount_price) || $discount_price < 0) send_error('সঠিক ছাড় মূল্য দিন।');

// যদি main_price খালি থাকে বা 0 হয়, তবে এটিকে NULL সেট করুন
$main_price_to_db = (!empty($main_price) && is_numeric($main_price) && $main_price > 0) ? $main_price : NULL;

if (!isset($_FILES['product_image']) || $_FILES['product_image']['error'] != 0) send_error('প্রোডাক্টের ছবি আবশ্যক।');

$file = $_FILES['product_image'];
$upload_dir = '../products/'; 
if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0755, true)) {
        send_error(' "products" ফোল্ডার তৈরি করা যায়নি। দয়া করে ম্যানুয়ালি তৈরি করুন।', 500);
    }
}

if ($file['size'] > 2 * 1024 * 1024) send_error('ফাইল ২MB-এর বড় হতে পারবে না।');
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime_type = $finfo->file($file['tmp_name']);
if (!in_array($mime_type, ['image/jpeg', 'image/png', 'image/gif'])) send_error('শুধুমাত্র JPG, PNG, GIF ফাইল অনুমোদিত।');

$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$unique_name = uniqid('prod_') . time() . '.' . $extension;
$target_file = $upload_dir . $unique_name;

if (move_uploaded_file($file['tmp_name'], $target_file)) {
    $image_path_to_db = 'products/' . $unique_name; 
    try {
        // *** SQL আপডেট করা হয়েছে ***
        $sql = "INSERT INTO products (title, price, main_price, description, images, is_visible) VALUES (?, ?, ?, ?, ?, 1)";
        $pdo->prepare($sql)->execute([$title, $discount_price, $main_price_to_db, $description, $image_path_to_db]);
        
        echo json_encode(['success' => true, 'message' => 'প্রোডাক্ট সফলভাবে যোগ করা হয়েছে!']);
    } catch (PDOException $e) {
        unlink($target_file); 
        send_error('ডেটাবেস ত্রুটি: ' . $e->getMessage(), 500);
    }
} else {
    send_error('ফাইল আপলোড ব্যর্থ হয়েছে। ফোল্ডার পারমিশন চেক করুন।', 500);
}
?>