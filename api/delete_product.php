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

if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'অনুমতি নেই।']);
    exit;
}

$id = $_POST['id'] ?? 0;
if (empty($id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ভুল আইডি।']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT images FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $image_path = $stmt->fetchColumn();

    $sql = "DELETE FROM products WHERE id = ?";
    $pdo->prepare($sql)->execute([$id]);

    if ($image_path) {
        $file_to_delete = '../' . $image_path;
        if (file_exists($file_to_delete) && strpos($file_to_delete, 'https://') === false) {
            unlink($file_to_delete);
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'প্রোডাক্ট ডিলিট করা হয়েছে।']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'ডেটাবেস ত্রুটি।']);
}
?>