
<body>

    <div class="hero text-center">
        <div class="container">
            <h1 class="display-3">প্রিমিয়াম ঘড়ি কিনুন</h1>
            <p class="lead">আজই আপনার পছন্দের ঘড়িটি খুঁজে নিন।</p>
        </div>
    </div>

    <div class="container my-5">
        <h2 class="text-center mb-4">আমাদের কালেকশন</h2>
        <div class="row g-4" id="product-grid">
            <?php
            $products = [];
            try {
                $stmt = $pdo->query("SELECT * FROM products WHERE is_visible = 1 ORDER BY id ASC");
                $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (count($products) == 0) {
                    $products = [
                        ['id' => 0, 'title' => 'ডামি: ক্লাসিক অ্যানালগ', 'price' => 89.99, 'main_price' => 120.00, 'description' => "এটি একটি ডামি প্রোডাক্ট।\nপ্রথম লাইন।\nদ্বিতীয় লাইন।", 'images' => 'https://via.placeholder.com/300x300?text=Watch1'],
                        ['id' => 0, 'title' => 'ডামি: লাক্সারি স্টিল', 'price' => 199.50, 'main_price' => 250.00, 'description' => 'এটি একটি ডামি প্রোডাক্ট।', 'images' => 'https://via.placeholder.com/300x300?text=Watch2'],
                        ['id' => 0, 'title' => 'ডামি: স্পোর্টস ওয়াচ', 'price' => 75.00, 'main_price' => null, 'description' => 'এটি একটি ডামি প্রোডাক্ট।', 'images' => 'https://via.placeholder.com/300x300?text=Watch3']
                    ];
                }

                $count = 0;
                foreach ($products as $product) {
                    $hiddenClass = ($count >= 3) ? 'd-none extra-product' : '';
                    
                    $main_price = $product['main_price'];
                    $discount_price = $product['price'];
                    
                    $price_html = '';
                    if ($main_price > 0 && $main_price > $discount_price) {
                        $price_html = '<h6 class="card-subtitle main-price">৳' . htmlspecialchars($main_price) . '</h6>' .
                                      '<h5 class="card-subtitle discount-price mb-2">৳' . htmlspecialchars($discount_price) . '</h5>';
                    } else {
                        $price_html = '<div class="price-placeholder"></div>' .
                                      '<h5 class="card-subtitle discount-price mb-2">৳' . htmlspecialchars($discount_price) . '</h5>';
                    }

                    $button_html = '';
                    if ($product['id'] == 0) {
                        $button_html = '<button class="btn btn-primary mt-auto" disabled>এখনই কিনুন</button>';
                    } else {
                        $button_html = '<a href="order.php?product_id=' . $product['id'] . '" class="btn btn-primary mt-auto">এখনই কিনুন</a>';
                    }

                    echo '<div class="col-12 col-md-6 col-lg-4 product-card ' . $hiddenClass . '">';
                    echo '  <div class="card h-100 shadow-sm">';
                    echo '    <img src="' . htmlspecialchars($product['images']) . '" class="card-img-top" alt="' . htmlspecialchars($product['title']) . '">';
                    echo '    <div class="card-body d-flex flex-column">';
                    echo '      <h5 class="card-title">' . htmlspecialchars($product['title']) . '</h5>';
                    
                    echo $price_html;
                    
                    // // nl2br() ফাংশনটি যোগ করা হয়েছে
                    echo '      <p class="card-text flex-grow-1">' . nl2br(htmlspecialchars($product['description'])) . '</p>';
                    
                    echo $button_html;
                    
                    echo '    </div>';
                    echo '  </div>';
                    echo '</div>';
                    
                    $count++;
                }
            } catch (PDOException $e) {
                echo '<div class="alert alert-danger">ডেটাবেসের সাথে সংযোগ করা যায়নি।</div>';
            }
            ?>
        </div>

        <?php if (count($products) > 3): ?>
        <div class="text-center mt-4">
            <button id="see-more-btn" class="btn btn-outline-dark btn-lg">আরও দেখুন</button>
        </div>
        <?php endif; ?>
    </div>

    