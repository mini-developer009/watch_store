<?php
require_once '../config.php';
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
header('Content-Type: application/json');

function send_error($message, $code = 400) {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

if (!isset($_SESSION['admin_id'])) send_error('অনুমতি নেই।', 403);

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$slug = trim($_POST['slug'] ?? '');
$button_text = trim($_POST['button_text'] ?? '');
$product_id = !empty($_POST['product_id']) ? intval($_POST['product_id']) : NULL; // *** নতুন ফিল্ড ***

if (empty($title) || empty($slug) || empty($button_text)) send_error('টাইটেল, লিঙ্ক (Slug), এবং বাটন টেক্সট আবশ্যক।');
if (!preg_match('/^[a-z0-9-]+$/', $slug)) send_error('লিঙ্ক (Slug) শুধুমাত্র ইংরেজি ছোট হাতের অক্ষর, সংখ্যা এবং হাইফেন (-) হতে পারে।');

$uploaded_images = [];
$upload_dir = '../products/';

if (!isset($_FILES['images']) || !is_array($_FILES['images']['name']) || empty($_FILES['images']['name'][0])) {
    send_error('অন্তত একটি ছবি আপলোড করতে হবে।');
}

$file_count = count($_FILES['images']['name']);
for ($i = 0; $i < $file_count; $i++) {
    if ($_FILES['images']['error'][$i] === 0) {
        $file_tmp_name = $_FILES['images']['tmp_name'][$i];
        $file_name = $_FILES['images']['name'][$i];
        
        $extension = pathinfo($file_name, PATHINFO_EXTENSION);
        $unique_name = uniqid('page_') . time() . '_' . $i . '.' . $extension;
        $target_file = $upload_dir . $unique_name;

        if (move_uploaded_file($file_tmp_name, $target_file)) {
            $uploaded_images[] = 'products/' . $unique_name;
        } else {
            send_error('ছবি আপলোড ব্যর্থ হয়েছে। ফোল্ডার পারমিশন চেক করুন।');
        }
    }
}

if (empty($uploaded_images)) send_error('ছবি আপলোড সফল হয়নি।');

$images_json = json_encode($uploaded_images);

try {
    // *** SQL আপডেট করা হয়েছে ***
    $sql = "INSERT INTO landing_pages (slug, product_id, title, description, images, button_text) VALUES (?, ?, ?, ?, ?, ?)";
    $pdo->prepare($sql)->execute([$slug, $product_id, $title, $description, $images_json, $button_text]);
    
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $base_dir = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    if (substr($base_dir, -4) === '/api') { $base_dir = dirname($base_dir); }
    $base_dir = ($base_dir == '/' || $base_dir == '\\') ? '' : $base_dir;
    
    $page_link = $protocol . '://' . $host . $base_dir . '/page/' . $slug;

    echo json_encode(['success' => true, 'message' => 'পেজ তৈরি সফল!', 'link' => $page_link]);
} catch (PDOException $e) {
    if ($e->errorInfo[1] == 1062) {
        send_error('এই লিঙ্ক (Slug) bereits ব্যবহৃত হয়েছে। একটি ইউনিক লিঙ্ক দিন।');
    }
    send_error('ডেটাবেস ত্রুটি: ' . $e->getMessage(), 500);
}
?>