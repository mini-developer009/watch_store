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

function send_error($message, $code = 403) {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

// সিকিউরিটি: শুধুমাত্র সুপার অ্যাডমিন এই কাজটি করতে পারবে
if (!isset($_SESSION['admin_id']) || !$_SESSION['is_super']) {
    send_error('এই কাজটি করার জন্য আপনার অনুমতি নেই।');
}

$admin_id_to_delete = $_POST['id'] ?? 0;
if (empty($admin_id_to_delete)) {
    send_error('ভুল অ্যাডমিন আইডি।', 400);
}

if ($admin_id_to_delete == $_SESSION['admin_id']) {
    send_error('আপনি নিজেকে ডিলিট করতে পারবেন না।');
}

try {
    // শুধুমাত্র সাব-অ্যাডমিন ডিলিট করা যাবে (is_super = 0)
    $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ? AND is_super = 0");
    $stmt->execute([$admin_id_to_delete]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'অ্যাডমিন ডিলিট করা হয়েছে।']);
    } else {
        send_error('অ্যাডমিন খুঁজে পাওয়া যায়নি বা ডিলিট করা সম্ভব হয়নি।', 404);
    }
    
} catch (PDOException $e) {
    send_error('ডেটাবেস ত্রুটি।', 500);
}
?>