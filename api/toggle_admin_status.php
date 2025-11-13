<?php
require_once '../config.php';
header('Content-Type: application/json');

function send_error($message, $code = 403) {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

// সিকিউরিটি: শুধুমাত্র সুপার অ্যাডমিন এই কাজটি করতে পারবে
if (!isset($_SESSION['admin_id']) || !$_SESSION['is_super']) {
    send_error('এই কাজটি করার জন্য আপনার অনুমতি নেই।');
}

$admin_id_to_toggle = $_POST['id'] ?? 0;
if (empty($admin_id_to_toggle)) {
    send_error('ভুল অ্যাডমিন আইডি।', 400);
}

try {
    // বর্তমান স্ট্যাটাস দেখুন
    $stmt = $pdo->prepare("SELECT is_active FROM admins WHERE id = ? AND is_super = 0");
    $stmt->execute([$admin_id_to_toggle]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        send_error('সাব-অ্যাডমিন খুঁজে পাওয়া যায়নি।', 404);
    }

    // স্ট্যাটাস টগল করুন (1 থেকে 0, অথবা 0 থেকে 1)
    $new_status = $admin['is_active'] ? 0 : 1;
    
    $update_stmt = $pdo->prepare("UPDATE admins SET is_active = ? WHERE id = ?");
    $update_stmt->execute([$new_status, $admin_id_to_toggle]);
    
    echo json_encode(['success' => true, 'new_status' => $new_status]);
    
} catch (PDOException $e) {
    send_error('ডেটাবেস ত্রুটি।', 500);
}
?>