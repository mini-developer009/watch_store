<?php
require_once '../config.php';
header('Content-Type: application/json');
function send_error($message, $code = 403) {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

// --- নিষ্ক্রিয় (Deactivated) অ্যাডমিন চেক ---
if (!isset($_SESSION['admin_id'])) {
    send_error('অনুমতি নেই।');
}
// শুধুমাত্র সুপার অ্যাডমিন এই কাজটি করতে পারবে
if (!$_SESSION['is_super']) {
    send_error('এই কাজটি করার জন্য আপনার অনুমতি নেই।');
}
// (সুপার অ্যাডমিনদের is_active চেক করার দরকার নেই)

$name = trim($_POST['name'] ?? '');
$employee_id = trim($_POST['employee_id'] ?? '');
$password = trim($_POST['password'] ?? ''); // নতুন পাসওয়ার্ড

if (empty($name) || empty($employee_id)) {
    send_error('নাম এবং এমপ্লয়ী আইডি আবশ্যক।', 400);
}
if (empty($password)) {
    send_error('পাসওয়ার্ড আবশ্যক।', 400);
}
if (strlen($password) < 6) {
    send_error('পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে।', 400);
}

// পাসওয়ার্ড হ্যাস করুন
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

try {
    $stmt = $pdo->prepare("SELECT id FROM admins WHERE employee_id = ?");
    $stmt->execute([$employee_id]);
    if ($stmt->fetch()) {
        send_error('এই এমপ্লয়ী আইডি bereits ব্যবহৃত হয়েছে।', 400);
    }

    // `is_active` = 1 (সচল) হিসেবে সেভ করুন
    $sql = "INSERT INTO admins (name, employee_id, password, is_super, is_active) VALUES (?, ?, ?, 0, 1)";
    $pdo->prepare($sql)->execute([$name, $employee_id, $hashed_password]);
    echo json_encode(['success' => true, 'message' => 'অ্যাডমিন সফলভাবে তৈরি করা হয়েছে!']);
} catch (PDOException $e) {
    send_error('ডেটাবেস ত্রুটি।', 500);
}
?>