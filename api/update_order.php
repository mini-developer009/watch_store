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
$status = $_POST['status'] ?? '';
if (empty($id) || !in_array($status, ['confirmed', 'shipped'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ভুল ইনপুট।']);
    exit;
}

try {
    $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$status, $id]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'ডেটাবেস ত্রুটি।']);
}
?>