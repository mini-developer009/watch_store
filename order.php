<?php
require_once 'config.php';

if (!isset($_GET['product_id']) || empty($_GET['product_id']) || $_GET['product_id'] == 0) {
    header('Location: index.php');
    exit;
}
$product_id = intval($_GET['product_id']);

try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$product) {
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    die("Error fetching product: " . $e->getMessage());
}

$main_price = $product['main_price'];
$discount_price = $product['price']; 

?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>আপনার অর্ডার কনফার্ম করুন</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: 'Hind Siliguri', sans-serif; }
        .main-price-order { font-size: 1rem; color: red; text-decoration: line-through; }
        .discount-price-order { font-size: calc(1rem + 5px); color: green; font-weight: 600; }
        
        
        /* --- নতুন CSS --- */
        .required-star {
            color: red;
            margin-left: 3px;
        }
    </style>
</head>
<body class="bg-light">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="text-center py-4">
                    <a href="index.php">
                        <img src="<?php echo htmlspecialchars($product['images']); ?>" 
                             alt="<?php echo htmlspecialchars($product['title']); ?>" 
                             style="max-height: 100px; max-width: 100px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd;">
                    </a>
                </div>

                <div id="success-message" class="alert alert-success d-none text-center" role="alert">
                    <h4 class="alert-heading">অর্ডার কনফার্ম হয়েছে!</h4>
                    <p>আপনার অর্ডার সফলভাবে সম্পন্ন হয়েছে। আমরা শীঘ্রই আপনার সাথে যোগাযোগ করবো। ধন্যবাদ!</p>
                    <hr>
                    <a href="index.php" class="btn btn-success">দোকানে ফিরে যান</a>
                    <a href="tel:01980599225" class="btn btn-info text-white">এখনই কল করুন</a>
                </div>

                <div class="card shadow-sm mb-5" id="order-container">
                    <div class="card-header bg-dark text-white"><h3 class="mb-0">আপনার অর্ডার কনফার্ম করুন</h3></div>
                    <div class="card-body p-4">
                        <div class="d-flex mb-4">
                            <img src="<?php echo htmlspecialchars($product['images']); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;">
                            <div class="ms-3">
                                <h4 class="mb-1"><?php echo htmlspecialchars($product['title']); ?></h4>
                                <p class="mb-1 text-muted"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                                <?php if ($main_price > 0 && $main_price > $discount_price): ?>
                                    <h6 class="main-price-order mb-0">৳<?php echo htmlspecialchars($main_price); ?></h6>
                                    <h5 class="discount-price-order">৳<?php echo htmlspecialchars($discount_price); ?></h5>
                                <?php else: ?>
                                    <h5 class="discount-price-order" style="margin-top: 24px;">৳<?php echo htmlspecialchars($discount_price); ?></h5>
                                <?php endif; ?>
                            </div>
                        </div>
                        <hr>
                        <form id="order-form">
                            <div id="form-alert" class="alert alert-danger d-none" role="alert"></div>
                            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">সম্পূর্ণ নাম<span class="required-star">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">ফোন নম্বর<span class="required-star">*</span></label>
                                <input type="tel" class="form-control" id="phone" name="phone" required maxlength="11" pattern="01[0-9]{9}" title="01... দিয়ে শুরু ১১ সংখ্যার নম্বর দিন">
                                <div class="form-text">১১ সংখ্যার সচল বাংলাদেশী নম্বর দিন (যেমন, 017...)</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="address" class="form-label">সম্পূর্ণ ঠিকানা<span class="required-star">*</span></label>
                                <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" id="submit-btn" class="btn btn-success btn-lg">
                                    <span id="submit-btn-text">এখনই কনফার্ম করুন</span>
                                    <span id="submit-btn-spinner" class="spinner-border spinner-border-sm d-none"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="otpModal" tabindex="-1" aria-labelledby="otpModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title" id="otpModalLabel">আপনার ফোন নম্বর ভেরিফাই করুন</h5></div>
                <div class="modal-body">
                    <p>আপনার নম্বরে একটি OTP পাঠানো হয়েছে। দয়া করে নিচে প্রবেশ করান।</p>
                    <div id="otp-alert" class="alert alert-danger d-none" role="alert"></div>
                    <form id="otp-form">
                        <div class="mb-3">
                            <label for="otp_code" class="form-label">৬-সংখ্যার OTP</label>
                            <input type="text" class="form-control" id="otp_code" name="otp_code" required maxlength="6">
                        </div>
                        <button type="submit" id="verify-otp-btn" class="btn btn-primary w-100">
                            <span id="verify-otp-btn-text">ভেরিফাই</span>
                            <span id="verify-otp-btn-spinner" class="spinner-border spinner-border-sm d-none"></span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        const otpModal = new bootstrap.Modal(document.getElementById('otpModal'));
        
        $('#order-form').on('submit', function(e) {
            e.preventDefault();
            const phone = $('#phone').val(); 
            const name = $('#name').val(); 
            const address = $('#address').val();
            
            if (name.trim() === '' || address.trim() === '') { 
                showAlert('form-alert', 'সম্পূর্ণ নাম এবং ঠিকানা আবশ্যক।'); 
                return; 
            }
            
            // --- *** জাভাস্ক্রিপ্ট ভ্যালিডেশন পরিবর্তন *** ---
            var phoneRegex = /^01[0-9]{9}$/; // 01 দিয়ে শুরু এবং মোট ১১ সংখ্যা
            if (!phoneRegex.test(phone)) {
                showAlert('form-alert', 'দয়া করে ১১ সংখ্যার সঠিক বাংলাদেশী নম্বর দিন (01... দিয়ে শুরু)।');
                return;
            }
            // --- *** ভ্যালিডেশন শেষ *** ---

            setButtonLoading('submit-btn', true);
            $.ajax({
                type: 'POST', url: 'api/send_otp.php', data: $(this).serialize(), dataType: 'json',
                success: (res) => { if (res.success) otpModal.show(); else showAlert('form-alert', res.message || 'OTP পাঠাতে ব্যর্থ হয়েছে।'); },
                error: (xhr) => showAlert('form-alert', xhr.responseJSON?.message || 'একটি ত্রুটি ঘটেছে।')
            }).always(() => setButtonLoading('submit-btn', false));
        });
        
        $('#otp-form').on('submit', function(e) {
            e.preventDefault();
            if ($('#otp_code').val().length !== 6) { showAlert('otp-alert', 'OTP অবশ্যই ৬ সংখ্যার হতে হবে।'); return; }
            setButtonLoading('verify-otp-btn', true);
            $.ajax({
                type: 'POST', url: 'api/verify_otp.php', data: { otp_code: $('#otp_code').val() }, dataType: 'json',
                success: (res) => {
                    if (res.success) {
                        otpModal.hide(); $('#order-container').addClass('d-none'); $('#success-message').removeClass('d-none');
                    } else showAlert('otp-alert', res.message || 'ভুল OTP।');
                },
                error: (xhr) => showAlert('alert-otp', xhr.responseJSON?.message || 'একটি ত্রুটি ঘটেছে।')
            }).always(() => setButtonLoading('verify-otp-btn', false));
        });
        
        function setButtonLoading(btnId, isLoading) {
            const btn = $('#' + btnId);
            btn.prop('disabled', isLoading);
            btn.find('#' + btnId + '-text').toggleClass('d-none', isLoading);
            btn.find('#' + btnId + '-spinner').toggleClass('d-none', !isLoading);
        }
        function showAlert(alertId, msg) { $('#' + alertId).text(msg).removeClass('d-none'); }
    });
    </script>
</body>
</html>