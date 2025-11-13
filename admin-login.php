<?php
require_once 'config.php';

if (isset($_SESSION['admin_id'])) {
    header('Location: admin.php');
    exit;
}

$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = trim($_POST['employee_id'] ?? '');
    $password = trim($_POST['password'] ?? ''); 

    if (empty($employee_id) || empty($password)) {
        $error_message = 'এমপ্লয়ী আইডি এবং পাসওয়ার্ড দিন।';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE employee_id = ?");
            $stmt->execute([$employee_id]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC); 

            if ($admin && password_verify($password, $admin['password'])) {
                // *** নতুন: অ্যাকাউন্ট নিষ্ক্রিয় (deactivated) কিনা তা পরীক্ষা করুন ***
                if ($admin['is_active'] == 0) {
                    $error_message = 'আপনার অ্যাকাউন্টটি নিষ্ক্রিয় করা হয়েছে। অ্যাডমিনের সাথে যোগাযোগ করুন।';
                } else {
                    // পাসওয়ার্ড সঠিক এবং অ্যাকাউন্ট সচল
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_name'] = $admin['name'];
                    $_SESSION['is_super'] = (bool) $admin['is_super'];
                    $_SESSION['is_active'] = (bool) $admin['is_active']; // *** নতুন সেশন ***
                    header('Location: admin.php');
                    exit;
                }
            } else {
                $error_message = 'ভুল এমপ্লয়ী আইডি বা পাসওয়ার্ড।';
            }
        } catch (PDOException $e) {
            $error_message = 'ডেটাবেস ত্রুটি।';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>অ্যাডমিন লগইন</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { display: flex; align-items: center; justify-content: center; min-height: 100vh; background-color: #f8f9fa; font-family: 'Hind Siliguri', sans-serif; }
        .login-card { width: 100%; max-width: 400px; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h3 class="card-title text-center mb-4">অ্যাডমিন লগইন</h3>
                <?php if ($error_message): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label for="employee_id" class="form-label">এমপ্লয়ী আইডি</label>
                        <input type="text" class="form-control" id="employee_id" name="employee_id" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">পাসওয়ার্ড</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">লগইন</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>