<?php
require_once '../config.php';
header('Content-Type: application/json');

function send_error($message) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') send_error('ভুল অনুরোধ।');

// *** সেশন ডেটা চেক করুন ***
if (!isset($_SESSION['pending_order_id']) || !isset($_SESSION['otp_code'])) {
    send_error('সেশনের মেয়াদ শেষ। আবার চেষ্টা করুন।');
}
if (time() > $_SESSION['otp_expiry']) {
    unset($_SESSION['pending_order_id'], $_SESSION['otp_code'], $_SESSION['otp_expiry']);
    send_error('OTP-এর মেয়াদ শেষ। ফর্মটি আবার সাবমিট করুন।');
}

$user_otp = trim($_POST['otp_code'] ?? '');
$order_id = $_SESSION['pending_order_id'];

if ($user_otp == $_SESSION['otp_code']) {
    // *** নতুন লজিক: অর্ডারটিকে "Verified" করুন ***
    try {
        $sql = "UPDATE orders SET is_verified = 1 WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$order_id]);

        // সেশন পরিষ্কার করুন
        unset($_SESSION['pending_order_id'], $_SESSION['otp_code'], $_SESSION['otp_expiry']);
        
        echo json_encode(['success' => true, 'message' => 'অর্ডার কনফার্ম হয়েছে!']);
    } catch (PDOException $e) {
        send_error('ডেটাবেস আপডেট ত্রুটি।');
    }
} else {
    send_error('ভুল OTP। দয়া করে আবার চেষ্টা করুন।');
}
?>