<?php
require_once '../config.php';
header('Content-Type: application/json');

function send_error($message) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') send_error('ভুল অনুরোধ।');

$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$product_id = intval($_POST['product_id'] ?? 0);

if (empty($name) || empty($phone) || empty($address) || $product_id <= 0) send_error('সকল ঘর পূরণ করুন।');

// --- ফোন নম্বর ফরম্যাট ঠিক করা ---
$phone_formatted = '';
if (strlen($phone) == 11 && strpos($phone, '0') === 0) {
    $phone_formatted = '88' . $phone; // 017... কে 88017... বানানো হলো
} elseif (strlen($phone) == 13 && strpos($phone, '880') === 0) {
    $phone_formatted = '88' . ltrim($phone, '88'); // ডাবল 88 থাকলে ঠিক করা
} else {
    send_error('সঠিক ১১ সংখ্যার মোবাইল নম্বর দিন (01...)।');
}

// *** নতুন লজিক: অর্ডারটি "Not Verified" হিসেবে ডেটাবেসে সেভ করুন ***
try {
    $sql = "INSERT INTO orders (product_id, name, phone, address, status, is_verified) VALUES (?, ?, ?, ?, 'pending', 0)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$product_id, $name, $phone, $address]);
    $new_order_id = $pdo->lastInsertId(); // নতুন অর্ডার আইডি নিন
} catch (PDOException $e) {
    send_error('ডেটাবেস ত্রুটি: ' . $e->getMessage());
}

$otp_code = rand(100000, 999999);
$_SESSION['pending_order_id'] = $new_order_id; // *** শুধু অর্ডার আইডি সেভ করুন ***
$_SESSION['otp_code'] = $otp_code;
$_SESSION['otp_expiry'] = time() + 300; // 5 minutes

// --- SMS.NET.BD API কল ---
$apiKey = 'r8E8787c7Dybf2gVP5cJcJAweAfMxJa49Lk9T60k'; 
$message = "Your Watch Shop OTP is $otp_code. It is valid for 5 minutes.";
$url = 'https://api.sms.net.bd/sendsms?' . http_build_query([
    'api_key' => $apiKey,
    'msg' => $message,
    'to' => $phone_formatted
]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($curl_error) {
    send_error("cURL Error: " . $curl_error);
} elseif ($http_code == 200) {
    echo json_encode(['success' => true, 'message' => 'OTP সফলভাবে পাঠানো হয়েছে।']);
} else {
    send_error("SMS পাঠাতে ব্যর্থ হয়েছে। API Error Code: $http_code, Response: $response");
}
?>