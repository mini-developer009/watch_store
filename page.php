<?php
require_once 'config.php';

if (!isset($_GET['slug']) || empty($_GET['slug'])) {
    header('Location: index.php');
    exit;
}
$slug = $_GET['slug'];
$product_data = null; // লিঙ্ক করা প্রোডাক্টের তথ্য রাখার জন্য

try {
    $stmt = $pdo->prepare("SELECT * FROM landing_pages WHERE slug = ?");
    $stmt->execute([$slug]);
    $page = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$page) {
        http_response_code(404);
        die("পেজটি খুঁজে পাওয়া যায়নি।");
    }
    $images = json_decode($page['images']);
    $linked_product_id = $page['product_id'];

    // যদি কোনো প্রোডাক্ট লিঙ্ক করা থাকে, তার তথ্য (বিশেষ করে মূল্য) নিয়ে আসা
    if ($linked_product_id) {
        $stmt_prod = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt_prod->execute([$linked_product_id]);
        $product_data = $stmt_prod->fetch(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    die("ডেটাবেস ত্রুটি: " . $e->getMessage());
}

// সাইটের বেস পাথ ঠিক করা (লিঙ্ক ঠিক রাখার জন্য)
$base_url = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
if ($base_url == '/' || $base_url == '\\') { $base_url = ''; }
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: 'Hind Siliguri', sans-serif; }
        .page-title { color: red; font-weight: 700; line-height: 1.4; }
        .carousel-item img {
            max-height: 500px;
            object-fit: contain; /* ছবি ফিট থাকবে, কাটবে না */
            width: 100%;
            border-radius: 8px;
        }
        .description-box { white-space: pre-wrap; font-size: 1.1rem; }
        .call-bar { background-color: #222; color: white; padding: 10px 0; }
        /* মূল্য দেখানোর স্টাইল */
        .main-price-order {
            font-size: 1.2rem;
            color: red;
            text-decoration: line-through;
        }
        .discount-price-order {
            font-size: calc(1.2rem + 5px);
            color: green;
            font-weight: 700;
        }
    </style>
</head>
<body class="bg-light">

    <div class="container my-3" id="page-container">
        <h2 class="text-center page-title my-3"><?php echo htmlspecialchars($page['title']); ?></h2>
        
        <?php if (count($images) > 0): ?>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div id="pageImageCarousel" class="carousel slide bg-white" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        <?php foreach ($images as $index => $img): ?>
                        <div class="carousel-item <?php echo $index == 0 ? 'active' : ''; ?>">
                            <img src="<?php echo $base_url; ?>/<?php echo htmlspecialchars($img); ?>" class="d-block w-100" alt="Slide <?php echo $index + 1; ?>">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($images) > 1): ?>
                    <button class="carousel-control-prev" type="button" data-bs-target="#pageImageCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon" aria-hidden="true"></span></button>
                    <button class="carousel-control-next" type="button" data-bs-target="#pageImageCarousel" data-bs-slide="next"><span class="carousel-control-next-icon" aria-hidden="true"></span></button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="call-bar text-center my-4 rounded">
                    <h4 class="mb-0 text-white"><?php echo htmlspecialchars($page['button_text']); ?> - <a href="tel:01980599225" class="text-warning text-decoration-none">01980599225</a></h4>
                </div>
            </div>
        </div>
        
        <?php if (!empty($page['description'])): ?>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body description-box p-4">
                        <h4 class="card-title">বিবরণ</h4>
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($page['description'])); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="row my-4 justify-content-center">
            <div class="col-lg-8">
                <?php if ($linked_product_id && $product_data): ?>
                    <div class="card shadow-sm">
                        <div class="card-body text-center p-4">
                            <h4 class="mb-3">প্রোডাক্টটি অর্ডার করতে বাটনে ক্লিক করুন</h4>
                            
                            <?php
                            if ($product_data['main_price'] > 0) {
                                echo "<h5 class='main-price-order d-inline-block me-3'>৳" . htmlspecialchars($product_data['main_price']) . "</h5>";
                                echo "<h4 class='discount-price-order d-inline-block'>৳" . htmlspecialchars($product_data['price']) . "</h4>";
                            } else {
                                echo "<h4 class='discount-price-order'>৳" . htmlspecialchars($product_data['price']) . "</h4>";
                            }
                            ?>
                            
                            <div class="d-grid mt-3">
                                <a href="<?php echo $base_url; ?>/order.php?product_id=<?php echo $linked_product_id; ?>" class="btn btn-success btn-lg fs-4">
                                    <?php echo htmlspecialchars($page['button_text']); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center">
                        অর্ডার করতে, অনুগ্রহ করে সরাসরি কল করুন: <a href="tel:01980599225" class="alert-link">01980599225</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
</body>
</html>